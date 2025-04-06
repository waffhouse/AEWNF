<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Admin\AdminComponent;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;

class RoleList extends AdminComponent
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
    public $roleSearch = '';

    // For deletion
    public $deleteRoleId = null;

    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view roles'];
    }

    /**
     * Initialize component
     */
    protected function mountComponent(): void
    {
        $this->loadInitialRoles();
    }

    /**
     * Load initial roles data
     */
    public function loadInitialRoles(): void
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
            $query = $this->getRoleQuery();

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
            Log::error('Error loading roles: '.$e->getMessage());
            // Fail gracefully in production
            if (! app()->environment('production')) {
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
        if ($this->hasMorePages && ! $this->isLoading) {
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
     * Get the base query for roles with optimized field selection
     */
    private function getRoleQuery()
    {
        $query = Role::query();

        // Filter by search term if provided
        if (! empty($this->roleSearch)) {
            $query->where('name', 'like', '%'.$this->roleSearch.'%');
        }

        // Add eager loading with specific fields
        $query->with(['permissions:id,name']);

        // Add withCount to know how many users have this role
        $query->withCount('users');

        // Add sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    /**
     * Search roles with form submission
     */
    public function searchRoles()
    {
        $this->resetItems();
    }

    /**
     * Clear role search
     */
    public function clearRoleSearch()
    {
        $this->roleSearch = '';
        $this->resetItems();
    }

    /**
     * Open edit form for a role
     */
    public function openRoleEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        // Dispatch event to the form component
        $this->dispatch('open-role-edit', $id)->to('admin.roles.role-form');
    }

    /**
     * Confirm role deletion
     */
    public function confirmDeleteRole($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' confirming deletion of role ID: '.$id);

        // Store the ID and open the confirmation modal
        $this->deleteRoleId = $id;
        $this->dispatch('open-modal', 'delete-role-confirmation');
    }

    /**
     * Delete a role
     */
    public function deleteRole()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' deleting role ID: '.$this->deleteRoleId);

        if (! $this->deleteRoleId) {
            return;
        }

        try {
            $role = Role::findById($this->deleteRoleId);

            // Check if the role is in use by users
            if ($role->users()->count() > 0) {
                $this->flashError('Cannot delete the role "'.$role->name.'" because it is assigned to '.$role->users()->count().' user(s). Please remove the role from all users first.');

                return;
            }

            $role->delete();

            $this->flashSuccess('Role deleted successfully!');
            $this->dispatch('close-modal', 'delete-role-confirmation');

            // Reset the roles list to remove the deleted role
            $this->resetItems();
        } catch (\Exception $e) {
            Log::error('Role deletion failed: '.$e->getMessage());
            $this->flashError('Role deletion failed: '.$e->getMessage());
        }
    }

    /**
     * Listen for role created event
     */
    #[On('role-created')]
    public function handleRoleCreated($roleData = null)
    {
        // If we have role data, add it to the top of the list
        if ($roleData && isset($roleData['id'])) {
            $newRole = Role::with(['permissions:id,name'])
                ->withCount('users')
                ->select(['id', 'name', 'created_at', 'updated_at'])
                ->find($roleData['id']);

            if ($newRole) {
                // Add to beginning of list if sorted by newest first
                if ($this->sortField === 'created_at' && $this->sortDirection === 'desc') {
                    array_unshift($this->items, $newRole);
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
     * Listen for role updated event
     */
    #[On('role-updated')]
    public function handleRoleUpdated($roleData = null)
    {
        // If we have role data, update it in the list
        if ($roleData && isset($roleData['id'])) {
            $updatedRole = Role::with(['permissions:id,name'])
                ->withCount('users')
                ->select(['id', 'name', 'created_at', 'updated_at'])
                ->find($roleData['id']);

            if ($updatedRole) {
                // Find and update the role in the current list
                foreach ($this->items as $index => $role) {
                    if ($role->id === $updatedRole->id) {
                        $this->items[$index] = $updatedRole;

                        return;
                    }
                }
            }
        }

        // Fallback to reload if role not found in list or no data provided
        $this->resetItems();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.roles.role-list', [
            'roles' => $this->items,
        ]);
    }
}
