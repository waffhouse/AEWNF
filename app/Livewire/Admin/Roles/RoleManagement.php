<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Admin\AdminComponent;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManagement extends AdminComponent
{
    // For role management
    public $roleName;
    public $rolePermissions = [];
    public $editRoleId;
    public $editingRole = false;
    public $roleIdToDelete = null;
    
    // For infinite scroll
    public $roles = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    public int $perPage = 10;
    
    /**
     * Define the permissions required for this component
     * 
     * Users with 'access admin dashboard' should be able to view the roles
     * Users with 'manage roles' should be able to edit/create/delete roles
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'manage roles'];
    }
    
    protected function rules()
    {
        return [
            'roleName' => 'required|min:3|unique:roles,name,' . ($this->editRoleId ?? ''),
            'rolePermissions' => 'array',
        ];
    }
    
    public function mount()
    {
        parent::mount();
        $this->loadRoles();
    }
    
    /**
     * Load roles with offset-based pagination
     */
    public function loadRoles()
    {
        $this->isLoading = true;
        
        $query = Role::withCount('users')->with('permissions');
        
        // Get total count for informational purposes
        $this->totalCount = $query->count();
        
        // Get roles for current page
        $newRoles = $query->orderBy('name')
                        ->offset($this->loadedCount)
                        ->limit($this->perPage + 1) // get one extra to check if there are more
                        ->get();
        
        // Check if there are more roles
        $this->hasMorePages = $newRoles->count() > $this->perPage;
        
        // Remove the extra item we used to check for more
        if ($this->hasMorePages) {
            $newRoles = $newRoles->slice(0, $this->perPage);
        }
        
        // Append new roles to existing collection
        foreach ($newRoles as $role) {
            $this->roles[] = $role;
        }
        
        // Update loaded count
        $this->loadedCount += $newRoles->count();
        
        $this->isLoading = false;
    }
    
    /**
     * Load more roles when scrolling
     */
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadRoles();
        }
    }
    
    /**
     * Reset roles when filters change
     */
    public function resetRoles()
    {
        $this->roles = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadRoles();
    }
    
    public function render()
    {        
        return view('livewire.admin.roles.role-management', [
            'allPermissions' => Permission::all(),
        ]);
    }
    
    public function openRoleFormModal()
    {
        // Check for specific permission to manage roles
        $this->authorizeAction('manage roles');
        
        $this->resetRoleForm();
        $this->dispatch('open-role-form-modal');
    }
    
    public function createRole()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles');
        
        try {
            $this->validate([
                'roleName' => 'required|min:3|unique:roles,name',
                'rolePermissions' => 'array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Role creation validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $role = Role::create(['name' => $this->roleName]);
        
        // Ensure we're syncing with actual Permission models or their names, not just IDs
        if (!empty($this->rolePermissions)) {
            $permissions = Permission::whereIn('id', $this->rolePermissions)->get();
            $role->syncPermissions($permissions);
        }
        
        $this->resetRoleForm();
        $this->dispatch('close-role-form-modal');
        
        session()->flash('message', 'Role created successfully!');
        $this->dispatch('message', 'Role created successfully!');
    }
    
    public function editRoleModal($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles');
        
        $this->editRoleId = $id;
        
        $role = Role::with('permissions')->findOrFail($id);
        $this->roleName = $role->name;
        $this->rolePermissions = $role->permissions->pluck('id')->toArray();
        
        $this->dispatch('open-role-form-modal');
    }
    
    public function updateRole()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles');
        
        try {
            $this->validate([
                'roleName' => 'required|min:3|unique:roles,name,' . $this->editRoleId,
                'rolePermissions' => 'array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Role validation failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        $role = Role::findOrFail($this->editRoleId);
        $role->update(['name' => $this->roleName]);
        
        // Ensure we're syncing with actual Permission models or their names, not just IDs
        if (!empty($this->rolePermissions)) {
            $permissions = Permission::whereIn('id', $this->rolePermissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]); // Clear all permissions if none selected
        }
        
        $this->resetRoleForm();
        $this->dispatch('close-role-form-modal');
        
        session()->flash('message', 'Role updated successfully!');
        $this->dispatch('message', 'Role updated successfully!');
    }
    
    public function confirmDeleteRole($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles');
        
        $this->roleIdToDelete = $id;
        $this->dispatch('open-delete-modal', ['type' => 'role', 'id' => $id]);
    }
    
    public function deleteRole($id = null)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('manage roles');
        
        // Use either passed ID or stored ID
        $roleId = $id ?? $this->roleIdToDelete;
        if (!$roleId) {
            return;
        }
        
        try {
            $role = Role::findOrFail($roleId);
            
            // Check if the role is in use by users
            if ($role->users()->count() > 0) {
                session()->flash('error', 'Cannot delete the role "' . $role->name . '" because it is assigned to ' . $role->users()->count() . ' user(s). Please remove the role from all users first.');
                $this->dispatch('error', 'Cannot delete the role "' . $role->name . '" because it is assigned to ' . $role->users()->count() . ' user(s). Please remove the role from all users first.');
                return;
            }
            
            // Check if role has permissions
            if ($role->permissions()->count() > 0) {
                session()->flash('error', 'Cannot delete the role "' . $role->name . '" because it has ' . $role->permissions()->count() . ' permission(s) assigned. Please remove all permissions from this role first, or choose to delete the permissions with the role.');
                $this->dispatch('error', 'Cannot delete the role "' . $role->name . '" because it has ' . $role->permissions()->count() . ' permission(s) assigned. Please remove all permissions from this role first, or choose to delete the permissions with the role.');
                return;
            }
            
            $role->delete();
            
            $this->roleIdToDelete = null;
            session()->flash('message', 'Role deleted successfully!');
            $this->dispatch('message', 'Role deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting role: ' . $e->getMessage());
            session()->flash('error', 'Error deleting role');
            $this->dispatch('error', 'Error deleting role');
        }
    }
    
    public function resetRoleForm()
    {
        $this->reset(['roleName', 'rolePermissions', 'editRoleId']);
        if (!$this->editingRole) {
            $this->editingRole = false;
        }
    }
}