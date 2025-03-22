<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Services\UserService;
use App\Traits\FormValidatable;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class UserForm extends AdminComponent
{
    use FormValidatable;
    
    // For user form
    public $name;
    public $email;
    public $password;
    public $userRole;
    public $customer_number;
    
    // UserService instance
    protected UserService $userService;
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view users'];
    }
    
    /**
     * Constructor to inject dependencies
     */
    public function boot()
    {
        $this->userService = new UserService();
    }
    
    /**
     * Cache for roles data to avoid repeated queries
     */
    protected $cachedRoles = null;
    
    /**
     * Get roles with caching to prevent redundant database queries
     */
    protected function getRoles()
    {
        if ($this->cachedRoles === null) {
            $this->cachedRoles = $this->userService->getAllRoles();
        }
        return $this->cachedRoles;
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.users.user-form', [
            'roles' => $this->getRoles(),
        ]);
    }
    
    /**
     * Define base validation rules that apply to both create and update
     */
    protected function baseRules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . ($this->editId ?? ''),
            'userRole' => 'required',
        ];
    }
    
    /**
     * Define rules that only apply when creating
     */
    protected function createRules(): array
    {
        $customerNumberRule = auth()->user()->hasPermissionTo('create users') 
            ? 'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number'
            : 'prohibited';
            
        return [
            'password' => 'required|min:8',
            'customer_number' => $customerNumberRule,
        ];
    }
    
    /**
     * Define rules that only apply when updating
     */
    protected function updateRules(): array
    {
        $customerNumberRule = auth()->user()->hasPermissionTo('edit users')
            ? 'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number,' . ($this->editId ?? '')
            : 'prohibited';
            
        return [
            'password' => 'nullable|min:8',
            'customer_number' => $customerNumberRule,
        ];
    }
    
    /**
     * Define custom validation messages
     */
    protected function customMessages(): array
    {
        return [
            'customer_number.regex' => 'The customer number must be a 4-digit number.',
            'customer_number.unique' => 'This customer number is already in use. Please assign a different number.'
        ];
    }
    
    /**
     * Open the form modal for creating a user
     * Listens for the open-new-user-form event from parent component
     */
    #[On('open-new-user-form')]
    public function openUserFormModal()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' opening user creation form');
        
        $this->enterCreateMode();
        $this->dispatch('open-modal', 'user-form-modal');
    }
    
    /**
     * Create a new user
     */
    public function createUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' creating new user');
        
        // Use the FormValidatable trait for validation
        if (!$this->validateForm()) {
            return;
        }
        
        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function() {
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password,
                ];
                
                // Only users with create users permission can set customer numbers, and only for customer roles
                if (auth()->user()->hasPermissionTo('create users') && 
                    (strpos($this->userRole, 'customer') !== false)) {
                    $userData['customer_number'] = $this->customer_number;
                }
                
                // Use the UserService to create the user
                $user = $this->userService->createUser($userData, $this->userRole);
                
                // Store the user ID to pass with event
                $this->newUserId = $user->id;
                
                return true;
            },
            'User created successfully!',
            'User creation failed'
        );
        
        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'user-form-modal');
            
            // Dispatch event to user-list component with the new user ID
            $this->dispatch('user-created', ['id' => $this->newUserId])->to('admin.users.user-list');
        }
    }
    
    /**
     * Listen for the event to open the edit form
     */
    #[On('open-user-edit')]
    public function openUserEdit($userId)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' opening edit form for user ID: ' . $userId);
        
        $this->enterEditMode($userId);
        $this->dispatch('open-modal', 'user-form-modal');
    }
    
    /**
     * Load a user record for editing
     */
    protected function loadRecord($id): void
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            $this->flashError('User not found');
            return;
        }
        
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->customer_number = $user->customer_number;
        $this->userRole = $user->roles->first() ? $user->roles->first()->name : '';
    }
    
    /**
     * Update an existing user
     */
    public function updateUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' updating user ID: ' . $this->editId);
        
        // Use the FormValidatable trait for validation
        if (!$this->validateForm()) {
            return;
        }
        
        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function() {
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                ];
                
                // Only include password if provided
                if ($this->password) {
                    $userData['password'] = $this->password;
                }
                
                // Only users with edit users permission can update customer numbers, and only for customer roles
                if (auth()->user()->hasPermissionTo('edit users') && 
                    (strpos($this->userRole, 'customer') !== false)) {
                    $userData['customer_number'] = $this->customer_number;
                }
                
                // Use the UserService to update the user
                $user = $this->userService->updateUser($this->editId, $userData, $this->userRole);
                
                return true;
            },
            'User updated successfully!',
            'User update failed'
        );
        
        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'user-form-modal');
            
            // Dispatch event to user-list component with the updated user ID
            $this->dispatch('user-updated', ['id' => $this->editId])->to('admin.users.user-list');
        }
    }
    
    /**
     * Reset the form to its default state
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'userRole', 'customer_number']);
        $this->formErrors = []; // Reset form errors directly
    }
}