<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = PermissionGroup::all()->keyBy('name');

        $permissions = [
            // User Management
            [
                'group' => 'User Management',
                'permissions' => [
                    ['name' => 'user.view', 'description' => 'View users list'],
                    ['name' => 'user.create', 'description' => 'Create new users'],
                    ['name' => 'user.edit', 'description' => 'Edit existing users'],
                    ['name' => 'user.delete', 'description' => 'Delete users'],
                ],
            ],
            // Role Management
            [
                'group' => 'Role Management',
                'permissions' => [
                    ['name' => 'role.view', 'description' => 'View roles list'],
                    ['name' => 'role.create', 'description' => 'Create new roles'],
                    ['name' => 'role.edit', 'description' => 'Edit existing roles'],
                    ['name' => 'role.delete', 'description' => 'Delete roles'],
                ],
            ],
            // Permission Management
            [
                'group' => 'Permission Management',
                'permissions' => [
                    ['name' => 'permission.view', 'description' => 'View permissions list'],
                    ['name' => 'permission.create', 'description' => 'Create new permissions'],
                    ['name' => 'permission.edit', 'description' => 'Edit existing permissions'],
                    ['name' => 'permission.delete', 'description' => 'Delete permissions'],
                    ['name' => 'permission-group.view', 'description' => 'View permission groups'],
                    ['name' => 'permission-group.create', 'description' => 'Create permission groups'],
                    ['name' => 'permission-group.edit', 'description' => 'Edit permission groups'],
                    ['name' => 'permission-group.delete', 'description' => 'Delete permission groups'],
                ],
            ],
            // Menu Management
            [
                'group' => 'Menu Management',
                'permissions' => [
                    ['name' => 'menu.view', 'description' => 'View menus list'],
                    ['name' => 'menu.create', 'description' => 'Create new menus'],
                    ['name' => 'menu.edit', 'description' => 'Edit existing menus'],
                    ['name' => 'menu.delete', 'description' => 'Delete menus'],
                ],
            ],
            // Route Access
            [
                'group' => 'Route Access',
                'permissions' => [
                    ['name' => 'route-access.view', 'description' => 'View route access list'],
                    ['name' => 'route-access.create', 'description' => 'Create route access'],
                    ['name' => 'route-access.edit', 'description' => 'Update route access'],
                    ['name' => 'route-access.delete', 'description' => 'Delete route access'],
                ],
            ],
            // Settings
            [
                'group' => 'Settings',
                'permissions' => [
                    ['name' => 'settings.view', 'description' => 'View application settings'],
                    ['name' => 'settings.edit', 'description' => 'Edit application settings'],
                ],
            ],
            // Activity Logs
            [
                'group' => 'Activity Logs',
                'permissions' => [
                    ['name' => 'activity-log.view', 'description' => 'View activity logs'],
                    ['name' => 'activity-log.delete', 'description' => 'Delete activity logs'],
                    ['name' => 'activity-log.export', 'description' => 'Export activity logs'],
                    ['name' => 'activity-log.clear', 'description' => 'Clear all activity logs'],
                ],
            ],
        ];

        foreach ($permissions as $groupData) {
            $group = $groups->get($groupData['group']);

            foreach ($groupData['permissions'] as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission['name'], 'guard_name' => 'web'],
                    [
                        'description' => $permission['description'],
                        'permission_group_id' => $group?->id,
                    ]
                );
            }
        }
    }
}
