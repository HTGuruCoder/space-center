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
            ['email' => 'bach@gmail.com'],
            [
                'first_name' => 'SARE BONI',
                'last_name' => 'Bachirou',
                'phone_number' => '+22966914511',
                'password' => Hash::make('12345678'),
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
