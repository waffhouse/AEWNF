<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;

class UserManagement extends AdminComponent
{
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view users'];
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.users.user-management');
    }
}
