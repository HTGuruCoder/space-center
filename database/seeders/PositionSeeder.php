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
        // Get first user for created_by
        $firstUser = User::first();

        if (!$firstUser) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $positions = [
            ['name' => 'Gondolero Venta en Linea', 'description' => ''],
            ['name' => 'Mensajeria', 'description' => ''],
            ['name' => 'Bodeguero Etiquetado', 'description' => ''],
            ['name' => 'Gerente General', 'description' => ''],
            ['name' => 'Bodeguero Fotos', 'description' => ''],
            ['name' => 'Supervisor Recursos Humanos', 'description' => ''],
            ['name' => 'Gondolero CLT', 'description' => ''],
            ['name' => 'Coordinador Financiero', 'description' => ''],
            ['name' => 'Patinador', 'description' => ''],
            ['name' => 'Supervisor VPV', 'description' => ''],
            ['name' => 'Diseños', 'description' => ''],
            ['name' => 'Bodeguero', 'description' => ''],
            ['name' => 'Supervisor OSC', 'description' => ''],
            ['name' => 'Creacion Videos', 'description' => ''],
            ['name' => 'Gondolero GM', 'description' => ''],
            ['name' => 'Gerente Financiera', 'description' => ''],
            ['name' => 'Coodinador General', 'description' => ''],
            ['name' => 'Programmer', 'description' => ''],
            ['name' => 'Recursos Humanos', 'description' => ''],
            ['name' => 'Jefe de Bodega', 'description' => ''],
            ['name' => 'Administrativo', 'description' => ''],
            ['name' => 'Gestor de Contenido', 'description' => ''],
            ['name' => 'Software engineer', 'description' => ''],
            ['name' => 'Incapacitado', 'description' => ''],
            ['name' => 'Verificador de Punto de Venta', 'description' => ''],
            ['name' => 'Supervisor de Bodega', 'description' => ''],
            ['name' => 'C. Logistica', 'description' => ''],
            ['name' => 'Encargado Virtual', 'description' => ''],
            ['name' => 'Encargado Cajero', 'description' => ''],
            ['name' => 'Supervisor Marketing', 'description' => ''],
        ];

        foreach ($positions as $position) {
            Position::create([
                'name' => $position['name'],
                'description' => $position['description'],
                'created_by' => $firstUser->id,
            ]);
        }

        $this->command->info('Successfully seeded ' . count($positions) . ' positions.');
    }
}
