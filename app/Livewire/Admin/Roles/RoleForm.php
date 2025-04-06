<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Admin\AdminComponent;
use App\Traits\FormValidatable;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm extends AdminComponent
{
    use FormValidatable;

    // Form fields
    public $name;

    public $permissions = [];

    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view roles'];
    }

    /**
     * Cache for permissions data to avoid repeated queries
     */
    protected $cachedPermissions = null;

    /**
     * Get permissions with caching to prevent redundant database queries
     */
    protected function getPermissions()
    {
        if ($this->cachedPermissions === null) {
            $this->cachedPermissions = Permission::select(['id', 'name'])->orderBy('name')->get();
        }

        return $this->cachedPermissions;
    }

    /**
     * Define base validation rules that apply to both create and update
     */
    protected function baseRules(): array
    {
        return [
            'name' => 'required|min:3|string|max:191',
            'permissions' => 'array',
        ];
    }

    /**
     * Define rules that only apply when creating
     */
    protected function createRules(): array
    {
        return [
            'name' => 'unique:roles,name',
        ];
    }

    /**
     * Define rules that only apply when updating
     */
    protected function updateRules(): array
    {
        return [
            'name' => 'unique:roles,name,'.($this->editId ?? ''),
        ];
    }

    /**
     * Open the form modal for creating a role
     */
    #[On('open-new-role-form')]
    public function openRoleFormModal()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' opening role creation form');

        $this->enterCreateMode();
        $this->dispatch('open-modal', 'role-form-modal');
    }

    /**
     * Create a new role
     */
    public function createRole()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' creating new role');

        // Use the FormValidatable trait for validation
        if (! $this->validateForm()) {
            return;
        }

        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function () {
                // Create the role
                $role = Role::create(['name' => $this->name]);

                // Sync permissions if any are selected
                if (! empty($this->permissions)) {
                    $permissionModels = Permission::whereIn('id', $this->permissions)->get();
                    $role->syncPermissions($permissionModels);
                }

                // Store the role ID to pass with event
                $this->newRoleId = $role->id;

                return true;
            },
            'Role created successfully!',
            'Role creation failed'
        );

        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'role-form-modal');

            // Dispatch event to role-list component with the new role ID
            $this->dispatch('role-created', ['id' => $this->newRoleId])->to('admin.roles.role-list');
        }
    }

    /**
     * Open the form modal for editing a role
     */
    #[On('open-role-edit')]
    public function openRoleEdit($roleId)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' opening edit form for role ID: '.$roleId);

        $this->enterEditMode($roleId);
        $this->dispatch('open-modal', 'role-form-modal');
    }

    /**
     * Load a role record for editing
     */
    protected function loadRecord($id): void
    {
        $role = Role::findById($id);
        if (! $role) {
            $this->flashError('Role not found');

            return;
        }

        // Load permissions relation
        $role->load('permissions');

        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('id')->toArray();
    }

    /**
     * Update an existing role
     */
    public function updateRole()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles', null, 'roles');

        $user = auth()->user();
        Log::info('User '.$user->name.' updating role ID: '.$this->editId);

        // Use the FormValidatable trait for validation
        if (! $this->validateForm()) {
            return;
        }

        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function () {
                // Get the role
                $role = Role::findById($this->editId);

                // Update the role name
                $role->update(['name' => $this->name]);

                // Sync permissions if any are selected
                $permissionModels = Permission::whereIn('id', $this->permissions)->get();
                $role->syncPermissions($permissionModels);

                return true;
            },
            'Role updated successfully!',
            'Role update failed'
        );

        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'role-form-modal');

            // Dispatch event to role-list component with the updated role ID
            $this->dispatch('role-updated', ['id' => $this->editId])->to('admin.roles.role-list');
        }
    }

    /**
     * Reset the form to its default state
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'permissions']);
        $this->formErrors = []; // Reset form errors
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.roles.role-form', [
            'availablePermissions' => $this->getPermissions(),
        ]);
    }
}
