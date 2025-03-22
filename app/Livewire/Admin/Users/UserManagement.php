<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagement extends AdminComponent
{
    // For user management
    public $name;
    public $email;
    public $password;
    public $userRole;
    public $customer_number;
    public $editUserId;
    public $userIdToDelete = null;
    public $userSearch = '';
    
    // For infinite scroll
    public $users = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    public int $perPage = 10;
    
    /**
     * Define the permissions required for this component
     * 
     * Only need one of these permissions to access the component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view users'];
    }
    
    protected function rules()
    {
        // User-related rules
        $customerNumberRule = 'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number,' . ($this->editUserId ?? '');
        if (!auth()->user()->hasPermissionTo($this->editUserId ? 'edit users' : 'create users')) {
            $customerNumberRule = 'prohibited';
        }
        
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . ($this->editUserId ?? ''),
            'password' => $this->editUserId ? 'nullable|min:8' : 'required|min:8',
            'userRole' => 'required',
            'customer_number' => $customerNumberRule
        ];
    }
    
    protected function messages()
    {
        return [
            'customer_number.regex' => 'The customer number must be a 4-digit number.',
            'customer_number.unique' => 'This customer number is already in use. Please assign a different number.'
        ];
    }
    
    public function mount()
    {
        parent::mount();
        $this->loadUsers();
    }
    
    /**
     * Load users with offset-based pagination
     */
    public function loadUsers()
    {
        $this->isLoading = true;
        
        $query = User::with('roles')
            ->when($this->userSearch, function($query) {
                return $query->where('name', 'like', '%' . $this->userSearch . '%')
                            ->orWhere('email', 'like', '%' . $this->userSearch . '%');
            });
        
        // Get total count for informational purposes
        $this->totalCount = $query->count();
        
        // Get users for current page
        $newUsers = $query->orderBy('name')
                        ->offset($this->loadedCount)
                        ->limit($this->perPage + 1) // get one extra to check if there are more
                        ->get();
        
        // Check if there are more users
        $this->hasMorePages = $newUsers->count() > $this->perPage;
        
        // Remove the extra item we used to check for more
        if ($this->hasMorePages) {
            $newUsers = $newUsers->slice(0, $this->perPage);
        }
        
        // Append new users to existing collection
        foreach ($newUsers as $user) {
            $this->users[] = $user;
        }
        
        // Update loaded count
        $this->loadedCount += $newUsers->count();
        
        $this->isLoading = false;
    }
    
    /**
     * Load more users when scrolling
     */
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadUsers();
        }
    }
    
    /**
     * Reset users when filters change
     */
    public function resetUsers()
    {
        $this->users = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadUsers();
    }
    
    public function updatedUserSearch()
    {
        $this->resetUsers();
    }
    
    public function render()
    {
        return view('livewire.admin.users.user-management', [
            'roles' => Role::all(),
            'paginator' => null, // Add this to prevent links() method call attempt
        ]);
    }
    
    public function openUserFormModal()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' opening user creation form');
        
        $this->resetUserForm();
        $this->dispatch('open-user-form-modal');
    }
    
    public function createUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' creating new user');
        
        try {
            // Apply only user-specific validation rules
            $this->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'userRole' => 'required',
                'customer_number' => auth()->user()->hasPermissionTo('create users') ? 
                    'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number' : 
                    'prohibited'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('User creation validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ];
        
        // Only users with create users permission can set customer numbers, and only for customers
        if (auth()->user()->hasPermissionTo('create users') && $this->userRole === 'customer') {
            $userData['customer_number'] = $this->customer_number;
        }
        
        $user = User::create($userData);
        
        $user->assignRole($this->userRole);
        
        $this->resetUserForm();
        $this->dispatch('close-user-form-modal');
        
        // Reset the users list to include the newly created user
        $this->resetUsers();
        
        session()->flash('message', 'User created successfully!');
        $this->dispatch('message', 'User created successfully!');
    }
    
    public function openUserEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' opening edit form for user ID: ' . $id);
        
        $user = User::findOrFail($id);
        $this->editUserId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->customer_number = $user->customer_number;
        $this->userRole = $user->roles->first() ? $user->roles->first()->name : '';
        
        $this->dispatch('open-user-form-modal');
    }
    
    public function updateUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' updating user ID: ' . $this->editUserId);
        
        \Log::info('updateUser method called with ID: ' . $this->editUserId);
        try {
            // Apply only user-specific validation rules
            $this->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email,' . $this->editUserId,
                'password' => 'nullable|min:8',
                'userRole' => 'required',
                'customer_number' => auth()->user()->hasPermissionTo('edit users') ? 
                    'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number,' . $this->editUserId : 
                    'prohibited'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $user = User::findOrFail($this->editUserId);
        
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        
        // Only users with edit users permission can update customer numbers, and only for customers
        if (auth()->user()->hasPermissionTo('edit users') && $this->userRole === 'customer') {
            $userData['customer_number'] = $this->customer_number;
        } elseif ($this->userRole !== 'customer') {
            // If changing role away from customer, remove customer number
            $userData['customer_number'] = null;
        }
        
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }
        
        $user->update($userData);
        
        // Sync the role
        $user->syncRoles([$this->userRole]);
        
        $this->resetUserForm();
        $this->dispatch('close-user-form-modal');
        
        // Reset the users list to reflect the updated user
        $this->resetUsers();
        
        session()->flash('message', 'User updated successfully!');
        $this->dispatch('message', 'User updated successfully!');
    }
    
    public function confirmDeleteUser($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' confirming deletion of user ID: ' . $id);
        
        $this->userIdToDelete = $id;
        
        // Use a direct confirmation with wire:confirm instead of a modal
        // The modal approach can be implemented if needed
        $this->deleteUser($id);
    }
    
    public function deleteUser($id = null)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        \Log::info('User ' . $user->name . ' deleting user ID: ' . ($id ?? $this->userIdToDelete));
        
        // Use either passed ID or stored ID
        $userId = $id ?? $this->userIdToDelete;
        if (!$userId) {
            return;
        }
        
        try {
            $user = User::findOrFail($userId);
            $user->delete();
            
            $this->userIdToDelete = null;
            
            // Reset the users list to remove the deleted user
            $this->resetUsers();
            
            session()->flash('message', 'User deleted successfully!');
            $this->dispatch('message', 'User deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            session()->flash('error', 'Error deleting user');
            $this->dispatch('error', 'Error deleting user');
        }
    }
    
    public function resetUserForm()
    {
        $this->reset(['name', 'email', 'password', 'userRole', 'customer_number', 'editUserId']);
    }
}