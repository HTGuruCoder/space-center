<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call(RoleAndPermissionSeeder::class);

        // Create super admin user
        $admin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '+22891504351',
            'timezone' => 'UTC',
            'birth_date' => '1990-01-01',
            'country_code' => 'TG',
            'currency_code' => 'XOF',
        ]);

        // Assign super admin role
        $admin->assignRole(RoleEnum::SUPER_ADMIN->value);

        $this->command->info('Super Admin created: admin@example.com / password');

        // Seed stores and positions
        $this->call([
            StoreSeeder::class,
            PositionSeeder::class,
        ]);
    }
}
