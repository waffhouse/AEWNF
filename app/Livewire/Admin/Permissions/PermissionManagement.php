<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\Admin\AdminComponent;

class PermissionManagement extends AdminComponent
{
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view permissions'];
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.permissions.permission-management');
    }
}