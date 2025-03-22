<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Admin\AdminComponent;

class RoleManagement extends AdminComponent
{
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view roles'];
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.roles.role-management');
    }
}