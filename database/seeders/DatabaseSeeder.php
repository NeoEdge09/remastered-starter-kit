<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(5000)->create();
        $this->call([
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            MenuSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
