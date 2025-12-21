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
                'first_name' => 'TCHETCHE',
                'last_name' => 'Herman',
                'phone_number' => '+22891504351',
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
            AbsenceTypeSeeder::class
        ]);
    }
}
