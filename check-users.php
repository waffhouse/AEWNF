<?php
// Check all users in the database
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Count users
$userCount = App\Models\User::count();
echo "Total users in database: {$userCount}\n\n";

// Get all users with their roles
$users = App\Models\User::with('roles')->get();

echo "USER LIST:\n";
echo str_repeat('-', 80) . "\n";
echo sprintf("%-4s | %-25s | %-30s | %-15s\n", "ID", "Name", "Email", "Role(s)");
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo sprintf("%-4s | %-25s | %-30s | %-15s\n", 
        $user->id, 
        substr($user->name, 0, 25), 
        substr($user->email, 0, 30), 
        substr($roles, 0, 15)
    );
}

echo str_repeat('-', 80) . "\n\n";

// Check database tables directly
echo "DIRECT DATABASE CHECK:\n";
echo str_repeat('-', 80) . "\n";

$dbUsers = DB::table('users')
    ->select('id', 'name', 'email', 'customer_number')
    ->orderBy('name')
    ->get();

echo "Total users from direct query: " . count($dbUsers) . "\n\n";

echo sprintf("%-4s | %-25s | %-30s | %-15s\n", "ID", "Name", "Email", "Customer #");
echo str_repeat('-', 80) . "\n";

foreach ($dbUsers as $user) {
    echo sprintf("%-4s | %-25s | %-30s | %-15s\n", 
        $user->id, 
        substr($user->name, 0, 25), 
        substr($user->email, 0, 30), 
        $user->customer_number ?? 'N/A'
    );
}

echo str_repeat('-', 80) . "\n";

// Check role assignments directly
echo "\nROLE ASSIGNMENTS CHECK:\n";
echo str_repeat('-', 80) . "\n";

$roleAssignments = DB::table('model_has_roles')
    ->join('users', 'model_has_roles.model_id', '=', 'users.id')
    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name')
    ->where('model_has_roles.model_type', 'App\\Models\\User')
    ->orderBy('users.name')
    ->get();

echo sprintf("%-4s | %-25s | %-30s | %-15s\n", "ID", "Name", "Email", "Role");
echo str_repeat('-', 80) . "\n";

foreach ($roleAssignments as $assignment) {
    echo sprintf("%-4s | %-25s | %-30s | %-15s\n", 
        $assignment->id, 
        substr($assignment->name, 0, 25), 
        substr($assignment->email, 0, 30), 
        substr($assignment->role_name, 0, 15)
    );
}

echo str_repeat('-', 80) . "\n";