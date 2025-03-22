<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class UserList extends AdminComponent
{
    use WithPagination;
    
    // For sorting
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // For filtering
    public $userSearch = '';
    
    // For deletion
    public $deleteUserId = null;
    
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
     * Sort items by field
     */
    public function sortBy(string $field)
    {
        // Toggle sort direction if clicking the same field
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        // Reset pagination to first page
        $this->resetPage();
    }
    
    /**
     * When search changes, reset pagination
     */
    public function updatedUserSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Open edit form for a user
     */
    public function openUserEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        // Dispatch event to the form component
        $this->dispatch('open-user-edit', $id)->to('admin.users.user-form');
    }
    
    /**
     * Confirm user deletion
     */
    public function confirmDeleteUser($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' confirming deletion of user ID: ' . $id);
        
        // Store the ID and open the confirmation modal
        $this->deleteUserId = $id;
        $this->dispatch('open-modal', 'delete-user-confirmation');
    }
    
    /**
     * Delete a user
     */
    public function deleteUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' deleting user ID: ' . $this->deleteUserId);
        
        if (!$this->deleteUserId) {
            return;
        }
        
        try {
            // Use the UserService to delete the user
            $this->userService->deleteUser($this->deleteUserId);
            
            $this->flashSuccess('User deleted successfully!');
            $this->dispatch('close-modal', 'delete-user-confirmation');
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());
            $this->flashError('User deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Listen for user created/updated events
     * No need to manually update the list - just let the pagination refresh
     */
    #[On('user-created')]
    #[On('user-updated')]
    #[On('users-refreshed')]
    public function refreshUserList()
    {
        // The list will refresh automatically on the next render
        // This empty method is just to catch the events
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        $users = $this->userService->getPaginatedUsers(
            10, // per page
            ['search' => $this->userSearch],
            [
                'with' => ['roles'],
                'orderBy' => $this->sortField,
                'direction' => $this->sortDirection
            ]
        );
        
        return view('livewire.admin.users.user-list', [
            'users' => $users,
        ]);
    }
}