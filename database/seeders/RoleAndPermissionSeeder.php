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

        // Create a super admin user first if doesn't exist
        $firstUser = \App\Models\User::firstOrCreate(
            ['email' => 'tchetcheherman@gmail.com'],
            [
                'first_name' => 'TCHETCHE',
                'last_name' => 'Herman',
                'phone_number' => '+22891504351',
                'password' => bcrypt('password'),
                'timezone' => 'UTC',
                'currency_code' => 'XOF',
                'country_code' => 'TG',
            ]
        );

        // Create Super Admin role first (no permissions assigned - bypasses all checks via Gate)
        Role::firstOrCreate([
            'name' => RoleEnum::SUPER_ADMIN->value,
            'guard_name' => 'web',
            'created_by' => $firstUser->id,
        ]);

        // Create Employee role
        $employeeRole = Role::firstOrCreate([
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

        // Assign permissions to Employee role
        $employeeRole->syncPermissions(PermissionEnum::forEmployee());

        // Assign super admin role to the first user
        if (!$firstUser->hasRole(RoleEnum::SUPER_ADMIN->value)) {
            $firstUser->assignRole(RoleEnum::SUPER_ADMIN->value);
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin user created: admin@example.com / password');
        $this->command->info('Super Admin role created (bypasses all permission checks via Gate)');
        $this->command->info('Employee role has ' . count(PermissionEnum::forEmployee()) . ' permissions');
    }
}
