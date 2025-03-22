<?php
// Create an admin user
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear the permission cache
app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

// Delete the user if it already exists (optional)
$existingUser = \App\Models\User::where('email', 'admin@example.com')->first();
if ($existingUser) {
    echo "Deleting existing admin user...\n";
    $existingUser->delete();
}

// Create a new admin user
$admin = new \App\Models\User([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);

// Save the user
$result = $admin->save();
echo "User created: " . ($result ? "Yes" : "No") . "\n";

// Check if the admin role exists
$adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
echo "Admin role exists: " . ($adminRole ? "Yes (ID: {$adminRole->id})" : "No") . "\n";

// List all roles
$roles = \Spatie\Permission\Models\Role::all();
echo "Available roles:\n";
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}

// Assign admin role
try {
    $admin->assignRole('admin');
    echo "Role assigned successfully\n";
} catch (Exception $e) {
    echo "Error assigning role: " . $e->getMessage() . "\n";
}

// Verify role assignment
$hasRole = $admin->hasRole('admin');
echo "User has admin role: " . ($hasRole ? "Yes" : "No") . "\n";

// Check direct database entry
$roleAssignment = DB::table('model_has_roles')
    ->where('model_id', $admin->id)
    ->where('model_type', get_class($admin))
    ->first();
echo "Role assignment in database: " . ($roleAssignment ? "Yes" : "No") . "\n";

if ($roleAssignment) {
    echo "Database role_id: {$roleAssignment->role_id}\n";
}

// Show login information
echo "\nYou can now log in with:\n";
echo "Email: admin@example.com\n";
echo "Password: password\n";