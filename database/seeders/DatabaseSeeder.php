<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);
        
        // Comment out user creation for now
        /*
        // Skip creating test users in production
        if (app()->environment() !== 'production') {
            // Only create demo users manually without using factories
            $admin = new User([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $admin->save();
            $admin->assignRole('admin');
            
            $staff = new User([
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $staff->save();
            $staff->assignRole('staff');
            
            $customer = new User([
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'customer_number' => null, // Explicitly null, to be assigned by admin
            ]);
            $customer->save();
            $customer->assignRole('customer');
        }
        */
    }
}
