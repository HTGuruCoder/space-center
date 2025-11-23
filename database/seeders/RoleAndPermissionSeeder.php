<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get the first user (should be created in DatabaseSeeder)
        $firstUser = \App\Models\User::first();

        if (!$firstUser) {
            $this->command->error('No user found! Please create a user first in DatabaseSeeder.');
            return;
        }

        // Create Super Admin role first (no permissions assigned - bypasses all checks via Gate)
        Role::firstOrCreate([
            'name' => RoleEnum::SUPER_ADMIN->value,
            'guard_name' => 'web',
            'created_by' => $firstUser->id,
        ]);

        // Create Employee role
        Role::firstOrCreate([
            'name' => RoleEnum::EMPLOYEE->value,
            'guard_name' => 'web',
            'created_by' => $firstUser->id,
        ]);

        // Create all permissions from PermissionEnum
        foreach (PermissionEnum::values() as $permissionValue) {
            Permission::firstOrCreate([
                'name' => $permissionValue,
                'guard_name' => 'web',
                'created_by' => $firstUser->id,
            ]);
        }

        // Assign super admin role to the first user
        if (!$firstUser->hasRole(RoleEnum::SUPER_ADMIN->value)) {
            $firstUser->assignRole(RoleEnum::SUPER_ADMIN->value);
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin role created (bypasses all permission checks via Gate)');
        $this->command->info('Employee role created (no permissions assigned by default)');
    }
}
