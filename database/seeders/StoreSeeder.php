<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        $stores = [
            [
                'name' => 'Main Store - Downtown',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [
                'name' => 'North Branch',
                'latitude' => 40.7589,
                'longitude' => -73.9851,
            ],
            [
                'name' => 'East Side Location',
                'latitude' => 40.7614,
                'longitude' => -73.9776,
            ],
            [
                'name' => 'West End Store',
                'latitude' => 40.7794,
                'longitude' => -73.9632,
            ],
            [
                'name' => 'South Branch',
                'latitude' => 40.7061,
                'longitude' => -74.0087,
            ],
        ];

        foreach ($stores as $store) {
            Store::create([
                'name' => $store['name'],
                'latitude' => $store['latitude'],
                'longitude' => $store['longitude'],
                'created_by' => $admin->id,
            ]);
        }
    }
}
