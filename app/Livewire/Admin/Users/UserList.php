<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class UserList extends AdminComponent
{
    // Define properties from InfiniteScrollable trait manually
    public array $items = [];
    public bool $hasMorePages = true;
    public bool $isLoading = false;
    public int $totalCount = 0;
    public int $loadedCount = 0;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    
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
     * Initialize component
     */
    protected function mountComponent(): void
    {
        $this->loadInitialUsers();
    }
    
    /**
     * Load initial users data
     */
    public function loadInitialUsers(): void
    {
        // Force a complete reset and reload of all items
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->totalCount = 0;
        $this->loadItems();
    }
    
    /**
     * Load items with cursor-based pagination for better performance
     */
    public function loadItems()
    {
        $this->isLoading = true;
        
        try {
            $query = $this->getUserQuery();
            
            // Clone query to avoid modifying the original
            $countQuery = clone $query;
            
            // Use a paginator instead of manual offset/limit for better performance
            // We get the total count and items in a single operation
            $perPage = isset($this->perPage) ? $this->perPage : 10;
            $paginator = $query->simplePaginate(
                $perPage, 
                ['id', 'name', 'email', 'customer_number', 'created_at', 'updated_at', 'last_refreshed_at'], 
                'page', 
                ceil($this->loadedCount / $perPage) + 1
            );
            
            // Only count total rows when needed (first page or when explicitly requested)
            // This avoids expensive COUNT(*) queries on large tables
            if ($this->loadedCount === 0) {
                $this->totalCount = $countQuery->count();
            }
            
            $newItems = $paginator->items();
            
            // Check if there are more pages directly from the paginator
            $this->hasMorePages = $paginator->hasMorePages();
            
            // Append new items to existing collection (only storing necessary data)
            foreach ($newItems as $item) {
                $this->items[] = $item;
            }
            
            // Update loaded count
            $this->loadedCount += count($newItems);
        } catch (\Exception $e) {
            Log::error('Error loading items: ' . $e->getMessage());
            // Fail gracefully in production
            if (!app()->environment('production')) {
                throw $e;
            }
        } finally {
            $this->isLoading = false;
        }
    }
    
    /**
     * Load more items when scrolling
     */
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadItems();
        }
    }
    
    /**
     * Reset items when filters change
     */
    public function resetItems()
    {
        // Force a complete reset and reload from the beginning
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->totalCount = 0;
        
        // Use a slight delay before loading to ensure all state is reset
        $this->loadItems();
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
        
        $this->resetItems();
    }
    
    /**
     * Get the base query for users with optimized field selection
     */
    private function getUserQuery()
    {
        return $this->userService->getUsersQuery(
            ['search' => $this->userSearch],
            [
                // Only select necessary fields to reduce data transfer
                'fields' => ['id', 'name', 'email', 'customer_number', 'created_at', 'updated_at', 'last_refreshed_at'],
                // Optimize eager loading to only fetch role name
                'with' => ['roles'],
                'orderBy' => $this->sortField,
                'direction' => $this->sortDirection
            ]
        );
    }
    
    /**
     * When search changes, reset the user list
     */
    public function updatedUserSearch()
    {
        $this->resetItems();
    }
    
    /**
     * Open edit form for a user
     */
    public function openUserEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        // Dispatch event to the form component
        // Use to instead of specifying component by name to ensure it reaches the right component
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
            
            // Complete reload of the user list to ensure proper state after deletion
            $this->loadInitialUsers();
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());
            $this->flashError('User deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Listen for user created event
     * Instead of reloading all items, just add the new user to the list
     */
    #[On('user-created')]
    public function handleUserCreated($userData = null)
    {
        // If we have user data, add it to the top of the list
        if ($userData && isset($userData['id'])) {
            $newUser = $this->userService->getUserById(
                $userData['id'], 
                ['roles'], 
                ['id', 'name', 'email', 'customer_number', 'created_at', 'updated_at', 'last_refreshed_at']
            );
            
            if ($newUser) {
                // Add to beginning of list if sorted by newest first
                if ($this->sortField === 'created_at' && $this->sortDirection === 'desc') {
                    array_unshift($this->items, $newUser);
                    $this->totalCount++;
                } else {
                    // Otherwise just reload the list to ensure proper sorting
                    $this->resetItems();
                }
            }
        } else {
            // Fallback to reload if no data provided
            $this->resetItems();
        }
    }
    
    /**
     * Listen for user updated event
     * Instead of reloading all items, just update the specific user in the list
     */
    #[On('user-updated')]
    public function handleUserUpdated($userData = null)
    {
        // If we have user data, update it in the list
        if ($userData && isset($userData['id'])) {
            $updatedUser = $this->userService->getUserById(
                $userData['id'], 
                ['roles'], 
                ['id', 'name', 'email', 'customer_number', 'created_at', 'updated_at', 'last_refreshed_at']
            );
            
            if ($updatedUser) {
                // Find and update the user in the current list
                foreach ($this->items as $index => $user) {
                    if ($user->id === $updatedUser->id) {
                        $this->items[$index] = $updatedUser;
                        return;
                    }
                }
            }
        }
        
        // Fallback to reload if user not found in list or no data provided
        $this->loadInitialUsers();
    }
    
    /**
     * Listen for users-refreshed event from the sync component
     * Reload the entire list when users are refreshed
     */
    #[On('users-refreshed')]
    public function handleUsersRefreshed()
    {
        // Reload all items when users are refreshed
        $this->loadInitialUsers();
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.users.user-list', [
            'users' => $this->items,
        ]);
    }
}