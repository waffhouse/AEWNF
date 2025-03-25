<?php
// Script to fix admin user role and permissions
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear permission cache
app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

// Get admin user
$admin = \App\Models\User::where('email', 'admin@example.com')->first();

if (!$admin) {
    echo "Admin user not found. Creating one...\n";
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
}

// Check if admin role exists
$adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();

if (!$adminRole) {
    echo "Admin role not found. Creating one...\n";
    $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    
    // Assign all permissions to admin role
    $permissions = \Spatie\Permission\Models\Permission::all();
    $adminRole->syncPermissions($permissions);
    echo "Created admin role with all permissions\n";
} else {
    echo "Admin role exists\n";
}

// Remove all previous roles from admin user
echo "Current roles: " . implode(', ', $admin->getRoleNames()->toArray()) . "\n";
foreach ($admin->roles as $role) {
    $admin->removeRole($role);
}

// Assign admin role to admin user
$admin->assignRole('admin');

// Verify role assignment
$hasRole = $admin->hasRole('admin');
echo "User has admin role: " . ($hasRole ? "Yes" : "No") . "\n";

// Check permissions
$permissions = $admin->getAllPermissions();
echo "Permissions count: " . count($permissions) . "\n";
echo "Has 'access admin dashboard' permission: " . ($admin->hasPermissionTo('access admin dashboard') ? "Yes" : "No") . "\n";

// Print important permissions
$keyPermissions = [
    'access admin dashboard',
    'view users',
    'create users',
    'edit users',
    'delete users',
    'manage roles',
    'manage permissions',
    'sync inventory',
    'manage orders',
    'view all orders'
];

echo "\nKey permissions check:\n";
foreach ($keyPermissions as $permission) {
    echo "- $permission: " . ($admin->hasPermissionTo($permission) ? "Yes" : "No") . "\n";
}

// Show login information
echo "\nYou can now log in with:\n";
echo "Email: admin@example.com\n";
echo "Password: password\n";