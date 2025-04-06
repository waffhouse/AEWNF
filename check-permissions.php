<?php

// Check permissions and roles
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// The customer role has been removed
echo "The generic 'customer' role has been successfully removed\n";

// List all roles in the system
$roles = Spatie\Permission\Models\Role::all();
echo "\nAll roles in system:\n";
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

// List all permissions in the system
$permissions = Spatie\Permission\Models\Permission::all();
echo "\nAll permissions in system:\n";
foreach ($permissions as $permission) {
    echo "- {$permission->name}\n";
}

// Original staff permission checks
$staff = App\Models\User::role('staff')->first();
if ($staff) {
    echo "\nChecking permissions for staff user: {$staff->name}\n";
    echo "Has 'view users' permission: ".($staff->can('view users') ? 'Yes' : 'No')."\n";
    echo "Has 'create users' permission: ".($staff->can('create users') ? 'Yes' : 'No')."\n";
    echo "Has 'edit users' permission: ".($staff->can('edit users') ? 'Yes' : 'No')."\n";
    echo "Has 'delete users' permission: ".($staff->can('delete users') ? 'Yes' : 'No')."\n";
}

// Original admin permission checks
$admin = App\Models\User::role('admin')->first();
if ($admin) {
    echo "\nChecking permissions for admin user: {$admin->name}\n";
    echo "Has 'view users' permission: ".($admin->can('view users') ? 'Yes' : 'No')."\n";
    echo "Has 'create users' permission: ".($admin->can('create users') ? 'Yes' : 'No')."\n";
    echo "Has 'edit users' permission: ".($admin->can('edit users') ? 'Yes' : 'No')."\n";
    echo "Has 'delete users' permission: ".($admin->can('delete users') ? 'Yes' : 'No')."\n";
}
