<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        $positions = [
            'Store Manager',
            'Assistant Manager',
            'Shift Supervisor',
            'Sales Associate',
            'Cashier',
            'Stock Clerk',
            'Customer Service Representative',
            'Inventory Manager',
            'Security Officer',
            'Janitor',
        ];

        foreach ($positions as $position) {
            Position::create([
                'name' => $position,
                'created_by' => $admin->id,
            ]);
        }
    }
}
