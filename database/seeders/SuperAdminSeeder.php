<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access',
            ]
        );

        // Create super admin agent
        $superAdmin = Agent::create([
            'name' => 'Super Admin',
            'email' => 'admin@rdagent.com',
            'password' => Hash::make('password'),
            'mobile_number' => '1234567890',
            'contact_info' => [
                'address' => 'System Address',
            ],
            'branch' => 'Head Office',
        ]);

        // Assign admin role to super admin
        $superAdmin->assignRole($adminRole);
    }
}
