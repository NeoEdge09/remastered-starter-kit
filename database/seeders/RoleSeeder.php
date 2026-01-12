<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin role (has all permissions by default via Gate::before)
        $superAdmin = Role::updateOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web'],
            ['name' => 'super-admin', 'guard_name' => 'web']
        );

        // Create Admin role with all permissions
        $admin = Role::updateOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // Assign all permissions to admin role
        $allPermissions = Permission::all();
        $admin->syncPermissions($allPermissions);

        // Create User role with limited permissions
        $user = Role::updateOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['name' => 'user', 'guard_name' => 'web']
        );

        // Users can only view their own profile (no admin permissions)
        $user->syncPermissions([]);
    }
}
