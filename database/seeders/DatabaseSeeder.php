<?php

namespace Database\Seeders;

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
        // Create a super admin user first if doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'tchetcheherman@gmail.com'],
            [
                'first_name' => 'Marck',
                'last_name' => 'Laurin',
                'phone_number' => '+2290160995224',
                'password' => Hash::make('marc2002'),
                'timezone' => 'UTC',
                'currency_code' => 'XOF',
                'country_code' => 'TG',
            ]
        );

        $this->command->info('Super Admin user created: ' . $superAdmin->email . ' / password');

        // Seed roles, permissions, stores and positions
        $this->call([
            RoleAndPermissionSeeder::class,
            StoreSeeder::class,
            PositionSeeder::class,
        ]);
    }
}