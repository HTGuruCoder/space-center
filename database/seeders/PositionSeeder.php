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
            'Gondolero Venta en Linea',
            'Mensajeria',
            'Bodeguero Etiquetado',
            'Gerente General',
            'Bodeguero Fotos',
            'Supervisor Recursos Humanos',
            'Gondolero CLT',
            'Coordinador Financiero',
            'Patinador',
            'Supervisor VPV',
            'Diseños',
            'Bodeguero',
            'Supervisor OSC',
            'Creacion Videos',
            'Gondolero GM',
            'Gerente Financiera',
            'Coodinador General',
            'Programmer',
            'Recursos Humanos',
            'Jefe de Bodega',
            'Administrativo',
            'Gestor de Contenido',
            'Software engineer',
            'Incapacitado',
            'Verificador de Punto de Venta',
            'Supervisor de Bodega',
            'C. Logistica',
            'Encargado Virtual',
            'Encargado Cajero',
            'Supervisor Marketing',
        ];

        foreach ($positions as $positionName) {
            Position::create([
                'name' => $positionName,
                'created_by' => $firstUser->id,
            ]);
        }

        $this->command->info('Successfully seeded ' . count($positions) . ' positions.');
    }
}
