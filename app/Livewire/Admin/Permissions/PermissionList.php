<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\Admin\AdminComponent;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class PermissionList extends AdminComponent
{
    // For infinite scroll
    public array $items = [];
    public bool $hasMorePages = true;
    public bool $isLoading = false;
    public int $totalCount = 0;
    public int $loadedCount = 0;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    
    // For filtering
    public $permissionSearch = '';
    
    // For deletion
    public $deletePermissionId = null;
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view permissions'];
    }
    
    /**
     * Initialize component
     */
    protected function mountComponent(): void
    {
        $this->loadInitialPermissions();
    }
    
    /**
     * Load initial permissions data
     */
    public function loadInitialPermissions(): void
    {
        $this->resetItems();
    }
    
    /**
     * Load items with pagination
     */
    public function loadItems()
    {
        $this->isLoading = true;
        
        try {
            $query = $this->getPermissionQuery();
            
            // Clone query to avoid modifying the original
            $countQuery = clone $query;
            
            // Use a paginator for better performance
            $paginator = $query->simplePaginate(
                $this->perPage, 
                ['id', 'name', 'created_at', 'updated_at'], 
                'page', 
                ceil($this->loadedCount / $this->perPage) + 1
            );
            
            // Only count total rows when needed
            if ($this->loadedCount === 0) {
                $this->totalCount = $countQuery->count();
            }
            
            $newItems = $paginator->items();
            
            // Check if there are more pages directly from the paginator
            $this->hasMorePages = $paginator->hasMorePages();
            
            // Append new items to existing collection
            foreach ($newItems as $item) {
                $this->items[] = $item;
            }
            
            // Update loaded count
            $this->loadedCount += count($newItems);
        } catch (\Exception $e) {
            Log::error('Error loading permissions: ' . $e->getMessage());
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
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
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
     * Get the base query for permissions with optimized field selection
     */
    private function getPermissionQuery()
    {
        $query = Permission::query();
        
        // Filter by search term if provided
        if (!empty($this->permissionSearch)) {
            $query->where('name', 'like', '%' . $this->permissionSearch . '%');
        }
        
        // Add eager loading with specific fields
        $query->with(['roles:id,name']);
        
        // Add sorting
        $query->orderBy($this->sortField, $this->sortDirection);
        
        return $query;
    }
    
    /**
     * When search changes, reset the permission list
     */
    public function updatedPermissionSearch()
    {
        $this->resetItems();
    }
    
    /**
     * Open edit form for a permission
     */
    public function openPermissionEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        // Dispatch event to the form component
        $this->dispatch('open-permission-edit', $id)->to('admin.permissions.permission-form');
    }
    
    /**
     * Confirm permission deletion
     */
    public function confirmDeletePermission($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' confirming deletion of permission ID: ' . $id);
        
        // Store the ID and open the confirmation modal
        $this->deletePermissionId = $id;
        $this->dispatch('open-modal', 'delete-permission-confirmation');
    }
    
    /**
     * Delete a permission
     */
    public function deletePermission()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' deleting permission ID: ' . $this->deletePermissionId);
        
        if (!$this->deletePermissionId) {
            return;
        }
        
        try {
            $permission = Permission::findById($this->deletePermissionId);
            
            // Check if the permission is in use by roles
            if ($permission->roles()->count() > 0) {
                $this->flashError('Cannot delete the permission "' . $permission->name . '" because it is assigned to ' . $permission->roles()->count() . ' role(s). Please remove the permission from all roles first.');
                return;
            }
            
            $permission->delete();
            
            $this->flashSuccess('Permission deleted successfully!');
            $this->dispatch('close-modal', 'delete-permission-confirmation');
            
            // Reset the permissions list to remove the deleted permission
            $this->resetItems();
        } catch (\Exception $e) {
            Log::error('Permission deletion failed: ' . $e->getMessage());
            $this->flashError('Permission deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Listen for permission created event
     */
    #[On('permission-created')]
    public function handlePermissionCreated($permissionData = null)
    {
        // If we have permission data, add it to the top of the list
        if ($permissionData && isset($permissionData['id'])) {
            $newPermission = Permission::with(['roles:id,name'])
                ->select(['id', 'name', 'created_at', 'updated_at'])
                ->find($permissionData['id']);
            
            if ($newPermission) {
                // Add to beginning of list if sorted by newest first
                if ($this->sortField === 'created_at' && $this->sortDirection === 'desc') {
                    array_unshift($this->items, $newPermission);
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
     * Listen for permission updated event
     */
    #[On('permission-updated')]
    public function handlePermissionUpdated($permissionData = null)
    {
        // If we have permission data, update it in the list
        if ($permissionData && isset($permissionData['id'])) {
            $updatedPermission = Permission::with(['roles:id,name'])
                ->select(['id', 'name', 'created_at', 'updated_at'])
                ->find($permissionData['id']);
            
            if ($updatedPermission) {
                // Find and update the permission in the current list
                foreach ($this->items as $index => $permission) {
                    if ($permission->id === $updatedPermission->id) {
                        $this->items[$index] = $updatedPermission;
                        return;
                    }
                }
            }
        }
        
        // Fallback to reload if permission not found in list or no data provided
        $this->resetItems();
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.permissions.permission-list', [
            'permissions' => $this->items,
        ]);
    }
}