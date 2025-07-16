<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access',
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Branch manager with limited administrative access',
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agent',
                'description' => 'Regular agent with basic access',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                ]
            );
        }
    }
} 