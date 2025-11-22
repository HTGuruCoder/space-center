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
            ['name' => 'Offer Shop', 'latitude' => 9.8955799, 'longitude' => -84.0863509],
            ['name' => 'El Cañonazo', 'latitude' => 9.9252075, 'longitude' => -84.0567476],
            ['name' => 'Promo Outlet', 'latitude' => 9.9021001, 'longitude' => -83.9969266],
            ['name' => 'Bodega 506', 'latitude' => 9.9783488, 'longitude' => -84.190116],
            ['name' => 'Amazing Gangas', 'latitude' => 9.9411265, 'longitude' => -84.0474482],
            ['name' => 'Hot Outlet', 'latitude' => 9.9706051, 'longitude' => -84.2211524],
            ['name' => 'Amazing Outlet', 'latitude' => 9.9348793, 'longitude' => -84.0776757],
            ['name' => 'Space center', 'latitude' => 9.92633409134571, 'longitude' => -84.0510240197182],
            ['name' => 'Bodega Principal', 'latitude' => 9.97924822525601, 'longitude' => -84.1827392578125],
            ['name' => 'Recursos Humanos/Financiero', 'latitude' => 9.9262548296296, 'longitude' => -84.0510910749436],
            ['name' => 'Marketing Space Center', 'latitude' => 9.92623633522641, 'longitude' => -84.0510588884354],
            ['name' => 'Amazing Offers', 'latitude' => 9.9416474, 'longitude' => -84.1238607],
            ['name' => 'Offer Store', 'latitude' => 9.9301982, 'longitude' => -84.1385628],
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
