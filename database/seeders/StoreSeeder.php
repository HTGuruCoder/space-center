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
        $firstUser = User::first();

        if (!$firstUser) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $stores = [
            ['name' => 'Centro Logistico Tegucigalpa', 'latitude' => 14.0723, 'longitude' => -87.1921],
            ['name' => 'Gondola Mall', 'latitude' => 14.10075, 'longitude' => -87.17283],
            ['name' => 'Gondola Palmira', 'latitude' => 14.08386, 'longitude' => -87.23155],
            ['name' => 'Gondola Altamira', 'latitude' => 14.10664, 'longitude' => -87.16832],
            ['name' => 'Gondola Comayaguela', 'latitude' => 14.08386, 'longitude' => -87.23155],
            ['name' => 'Gondola Olancho Shopping Center', 'latitude' => 14.63976, 'longitude' => -86.18758],
            ['name' => 'Gondola Juticalpa Bodega', 'latitude' => 14.62797, 'longitude' => -86.18972],
            ['name' => 'Gondola La Ceiba', 'latitude' => 15.77259, 'longitude' => -86.79626],
            ['name' => 'Gondola SPS', 'latitude' => 15.50417, 'longitude' => -88.02527],
            ['name' => 'Gondola SPS Bodega', 'latitude' => 15.50974, 'longitude' => -88.02193],
            ['name' => 'Gondola SPS Circunvalacion', 'latitude' => 15.48058, 'longitude' => -88.02025],
            ['name' => 'Gondola Tutule', 'latitude' => 13.31807, 'longitude' => -87.18262],
            ['name' => 'Venta por Internet', 'latitude' => 14.08386, 'longitude' => -87.23155],
        ];

        foreach ($stores as $store) {
            Store::create([
                'name' => $store['name'],
                'latitude' => $store['latitude'],
                'longitude' => $store['longitude'],
                'created_by' => $firstUser->id,
            ]);
        }

        $this->command->info('Successfully seeded ' . count($stores) . ' stores.');
    }
}
