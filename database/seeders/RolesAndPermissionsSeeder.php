<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role and permission management
            'manage roles',
            'manage permissions',

            // Admin dashboard
            'access admin dashboard',

            // Inventory management
            'sync inventory',

            // Customer management permissions
            'view customers',
            'sync customers',

            // Catalog and sales permissions
            'view catalog',
            'view sales history',

            // NetSuite Integration permissions
            'view netsuite sales data',
            'sync netsuite sales data',

            // Item visibility permissions
            'view unrestricted items',
            'view florida items',
            'view georgia items',

            // Cart and order permissions
            'add to cart',
            'place orders',
            'view own orders',
            'view all orders',
            'manage orders',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName);
        }

        // Create roles and assign permissions
        // Florida-specific customer role
        $floridaCustomerRole = Role::findOrCreate('florida customer');
        $floridaCustomerRole->syncPermissions([
            'view catalog',
            'view unrestricted items',
            'view florida items',
            'view sales history',
            'add to cart',
            'place orders',
            'view own orders',
        ]);

        // Georgia-specific customer role
        $georgiaCustomerRole = Role::findOrCreate('georgia customer');
        $georgiaCustomerRole->syncPermissions([
            'view catalog',
            'view unrestricted items',
            'view georgia items',
            'view sales history',
            'add to cart',
            'place orders',
            'view own orders',
        ]);

        $staffRole = Role::findOrCreate('staff');
        $staffRole->syncPermissions([
            'view users',
            'view catalog',
            'view unrestricted items',
            'view florida items',
            'view georgia items',
            'view sales history',
            'view netsuite sales data',
            'view all orders',
            'manage orders',
        ]);

        $adminRole = Role::findOrCreate('admin');
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Check if the old 'customer' role exists and migrate users
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            // Get all users with this role
            $users = User::role('customer')->get();

            // For each user, assign them to a state-specific role and update their state
            foreach ($users as $user) {
                // Determine which state to assign based on existing data or default to Florida
                $newRole = 'florida customer'; // Default
                $newState = 'Florida'; // Default

                // If user already has a state set, use that
                if ($user->state === 'Georgia') {
                    $newRole = 'georgia customer';
                    $newState = 'Georgia';
                }

                // Remove old role and assign new role
                $user->removeRole('customer');
                $user->assignRole($newRole);

                // Update state
                $user->state = $newState;
                $user->save();

                $this->command->info("Migrated user {$user->name} from 'customer' to '{$newRole}'");
            }

            // Now that all users have been migrated, delete the old role
            $customerRole->delete();
            $this->command->info("Removed deprecated 'customer' role");
        }
    }
}
