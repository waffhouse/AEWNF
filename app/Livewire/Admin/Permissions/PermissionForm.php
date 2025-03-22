<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\Admin\AdminComponent;
use App\Traits\FormValidatable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class PermissionForm extends AdminComponent
{
    use FormValidatable;
    
    // Form fields
    public $name;
    public $roles = [];
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view permissions'];
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
            $this->cachedRoles = Role::select(['id', 'name'])->orderBy('name')->get();
        }
        return $this->cachedRoles;
    }
    
    /**
     * Define base validation rules that apply to both create and update
     */
    protected function baseRules(): array
    {
        return [
            'name' => 'required|min:3|string|max:191',
            'roles' => 'array',
        ];
    }
    
    /**
     * Define rules that only apply when creating
     */
    protected function createRules(): array
    {
        return [
            'name' => 'unique:permissions,name',
        ];
    }
    
    /**
     * Define rules that only apply when updating
     */
    protected function updateRules(): array
    {
        return [
            'name' => 'unique:permissions,name,' . ($this->editId ?? ''),
        ];
    }
    
    /**
     * Open the form modal for creating a permission
     */
    #[On('open-new-permission-form')]
    public function openPermissionFormModal()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' opening permission creation form');
        
        $this->enterCreateMode();
        $this->dispatch('open-modal', 'permission-form-modal');
    }
    
    /**
     * Create a new permission
     */
    public function createPermission()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' creating new permission');
        
        // Use the FormValidatable trait for validation
        if (!$this->validateForm()) {
            return;
        }
        
        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function() {
                // Create the permission
                $permission = Permission::create(['name' => $this->name]);
                
                // Sync roles if any are selected
                if (!empty($this->roles)) {
                    $roleModels = Role::whereIn('id', $this->roles)->get();
                    foreach ($roleModels as $role) {
                        $role->givePermissionTo($permission->name);
                    }
                }
                
                // Store the permission ID to pass with event
                $this->newPermissionId = $permission->id;
                
                return true;
            },
            'Permission created successfully!',
            'Permission creation failed'
        );
        
        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'permission-form-modal');
            
            // Dispatch event to permission-list component with the new permission ID
            $this->dispatch('permission-created', ['id' => $this->newPermissionId])->to('admin.permissions.permission-list');
        }
    }
    
    /**
     * Open the form modal for editing a permission
     */
    #[On('open-permission-edit')]
    public function openPermissionEdit($permissionId)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' opening edit form for permission ID: ' . $permissionId);
        
        $this->enterEditMode($permissionId);
        $this->dispatch('open-modal', 'permission-form-modal');
    }
    
    /**
     * Load a permission record for editing
     */
    protected function loadRecord($id): void
    {
        $permission = Permission::findById($id);
        if (!$permission) {
            $this->flashError('Permission not found');
            return;
        }
        
        // Load roles relation
        $permission->load('roles');
        
        $this->name = $permission->name;
        $this->roles = $permission->roles->pluck('id')->toArray();
    }
    
    /**
     * Update an existing permission
     */
    public function updatePermission()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions', null, 'permissions');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' updating permission ID: ' . $this->editId);
        
        // Use the FormValidatable trait for validation
        if (!$this->validateForm()) {
            return;
        }
        
        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function() {
                // Get the permission
                $permission = Permission::findById($this->editId);
                
                // Update the permission name
                $permission->update(['name' => $this->name]);
                
                // Sync roles if any are selected
                // First, remove permission from all roles
                foreach ($permission->roles as $role) {
                    $role->revokePermissionTo($permission->name);
                }
                
                // Then add to selected roles
                if (!empty($this->roles)) {
                    $roleModels = Role::whereIn('id', $this->roles)->get();
                    foreach ($roleModels as $role) {
                        $role->givePermissionTo($permission->name);
                    }
                }
                
                return true;
            },
            'Permission updated successfully!',
            'Permission update failed'
        );
        
        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'permission-form-modal');
            
            // Dispatch event to permission-list component with the updated permission ID
            $this->dispatch('permission-updated', ['id' => $this->editId])->to('admin.permissions.permission-list');
        }
    }
    
    /**
     * Reset the form to its default state
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'roles']);
        $this->formErrors = []; // Reset form errors
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.permissions.permission-form', [
            'availableRoles' => $this->getRoles(),
        ]);
    }
}