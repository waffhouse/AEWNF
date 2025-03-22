<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\Admin\AdminComponent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionManagement extends AdminComponent
{
    // For permission management
    public $permissionName;
    public $permissionRoles = [];
    public $editPermissionId;
    public $editingPermission = false;
    public $permissionIdToDelete = null;
    
    // For infinite scroll
    public $permissions = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    public int $perPage = 10;
    
    /**
     * Define the permissions required for this component
     * 
     * Users with 'access admin dashboard' should be able to view the permissions
     * Users with 'manage permissions' should be able to edit/create/delete permissions
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'manage permissions'];
    }
    
    protected function rules()
    {
        return [
            'permissionName' => 'required|min:3|unique:permissions,name,' . ($this->editPermissionId ?? ''),
            'permissionRoles' => 'array',
        ];
    }
    
    public function mount()
    {
        parent::mount();
        $this->loadPermissions();
    }
    
    /**
     * Load permissions with offset-based pagination
     */
    public function loadPermissions()
    {
        $this->isLoading = true;
        
        $query = Permission::with('roles');
        
        // Get total count for informational purposes
        $this->totalCount = $query->count();
        
        // Get permissions for current page
        $newPermissions = $query->orderBy('name')
                        ->offset($this->loadedCount)
                        ->limit($this->perPage + 1) // get one extra to check if there are more
                        ->get();
        
        // Check if there are more permissions
        $this->hasMorePages = $newPermissions->count() > $this->perPage;
        
        // Remove the extra item we used to check for more
        if ($this->hasMorePages) {
            $newPermissions = $newPermissions->slice(0, $this->perPage);
        }
        
        // Append new permissions to existing collection
        foreach ($newPermissions as $permission) {
            $this->permissions[] = $permission;
        }
        
        // Update loaded count
        $this->loadedCount += $newPermissions->count();
        
        $this->isLoading = false;
    }
    
    /**
     * Load more permissions when scrolling
     */
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadPermissions();
        }
    }
    
    /**
     * Reset permissions when filters change
     */
    public function resetPermissions()
    {
        $this->permissions = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadPermissions();
    }
    
    public function render()
    {
        return view('livewire.admin.permissions.permission-management', [
            'allRoles' => Role::all(),
        ]);
    }
    
    public function openPermissionFormModal()
    {
        // Check for specific permission to manage permissions
        $this->authorizeAction('manage permissions');
        
        $this->resetPermissionForm();
        $this->dispatch('open-permission-form-modal');
    }
    
    public function createPermission()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions');
        
        try {
            $this->validate([
                'permissionName' => 'required|min:3|unique:permissions,name',
                'permissionRoles' => 'array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Permission creation validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $permission = Permission::create(['name' => $this->permissionName]);
        
        // Sync roles - need to use permission name, not the permission object
        if (!empty($this->permissionRoles)) {
            $roles = collect($this->permissionRoles)->map(function($id) {
                return Role::findById($id);
            });
            
            foreach ($roles as $role) {
                $role->givePermissionTo($permission->name);
            }
        }
        
        $this->resetPermissionForm();
        $this->dispatch('close-permission-form-modal');
        
        session()->flash('message', 'Permission created successfully!');
        $this->dispatch('message', 'Permission created successfully!');
    }
    
    public function editPermissionModal($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions');
        
        $this->editPermissionId = $id;
        
        $permission = Permission::with('roles')->findOrFail($id);
        $this->permissionName = $permission->name;
        $this->permissionRoles = $permission->roles->pluck('id')->toArray();
        
        $this->dispatch('open-permission-form-modal');
    }
    
    public function updatePermission()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions');
        
        try {
            $this->validate([
                'permissionName' => 'required|min:3|unique:permissions,name,' . $this->editPermissionId,
                'permissionRoles' => 'array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Permission validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $permission = Permission::findOrFail($this->editPermissionId);
        $permission->update(['name' => $this->permissionName]);
        
        // Sync roles - need to use collection of models instead of IDs
        $roles = collect($this->permissionRoles)->map(function($id) {
            return Role::findById($id);
        });
        
        // Detach all current roles and attach the selected ones
        $permission->roles()->detach();
        foreach ($roles as $role) {
            $role->givePermissionTo($permission->name);
        }
        
        $this->resetPermissionForm();
        $this->dispatch('close-permission-form-modal');
        
        session()->flash('message', 'Permission updated successfully!');
        $this->dispatch('message', 'Permission updated successfully!');
    }
    
    public function confirmDeletePermission($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions');
        
        $this->permissionIdToDelete = $id;
        $this->dispatch('open-delete-modal', ['type' => 'permission', 'id' => $id]);
    }
    
    public function deletePermission($id = null)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage permissions');
        
        // Use either passed ID or stored ID
        $permissionId = $id ?? $this->permissionIdToDelete;
        if (!$permissionId) {
            return;
        }
        
        try {
            $permission = Permission::findOrFail($permissionId);
            
            // Check if the permission is in use by a role
            if ($permission->roles()->count() > 0) {
                $roleNames = $permission->roles->pluck('name')->implode(', ');
                session()->flash('error', 'Cannot delete the permission "' . $permission->name . '" because it is assigned to these roles: ' . $roleNames . '. Please remove the permission from all roles first.');
                $this->dispatch('error', 'Cannot delete the permission "' . $permission->name . '" because it is assigned to these roles: ' . $roleNames . '. Please remove the permission from all roles first.');
                return;
            }
            
            $permission->delete();
            
            $this->permissionIdToDelete = null;
            session()->flash('message', 'Permission deleted successfully!');
            $this->dispatch('message', 'Permission deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting permission: ' . $e->getMessage());
            session()->flash('error', 'Error deleting permission');
            $this->dispatch('error', 'Error deleting permission');
        }
    }
    
    public function resetPermissionForm()
    {
        $this->reset(['permissionName', 'permissionRoles', 'editPermissionId']);
        if (!$this->editingPermission) {
            $this->editingPermission = false;
        }
    }
}