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

        // Create all permissions from PermissionEnum
        foreach (PermissionEnum::values() as $permissionValue) {
            Permission::firstOrCreate(['name' => $permissionValue]);
        }

        // Create Super Admin role (no permissions assigned - bypasses all checks via Gate)
        Role::firstOrCreate(['name' => RoleEnum::SUPER_ADMIN->value]);

        // Create Employee role with default permissions
        $employeeRole = Role::firstOrCreate(['name' => RoleEnum::EMPLOYEE->value]);
        $employeeRole->syncPermissions(PermissionEnum::forEmployee());

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin role created (bypasses all permission checks via Gate)');
        $this->command->info('Employee role has ' . count(PermissionEnum::forEmployee()) . ' permissions');
    }
}
