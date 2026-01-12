<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['name' => 'User Management', 'description' => 'Permissions related to user management', 'order' => 1],
            ['name' => 'Role Management', 'description' => 'Permissions related to role management', 'order' => 2],
            ['name' => 'Permission Management', 'description' => 'Permissions related to permission management', 'order' => 3],
            ['name' => 'Menu Management', 'description' => 'Permissions related to menu management', 'order' => 4],
            ['name' => 'Route Access', 'description' => 'Permissions related to route access management', 'order' => 5],
            ['name' => 'Settings', 'description' => 'Permissions related to application settings', 'order' => 6],
            ['name' => 'Activity Logs', 'description' => 'Permissions related to activity logs', 'order' => 7],
        ];

        foreach ($groups as $group) {
            PermissionGroup::updateOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}
