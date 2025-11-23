<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AbsenceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first super admin user for created_by field
        $superAdmin = User::role(RoleEnum::SUPER_ADMIN->value)->first();

        if (!$superAdmin) {
            $this->command->error('No super admin user found. Please run UserSeeder first.');
            return;
        }

        $absenceTypes = [
            // Breaks
            [
                'name' => 'Almuerzo',
                'is_paid' => true,
                'is_break' => true,
                'requires_validation' => false,
                'max_per_day' => 1,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Descanso',
                'is_paid' => true,
                'is_break' => true,
                'requires_validation' => false,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],

            // Vacaciones (Annual Paid Vacation - 2 weeks per year minimum)
            [
                'name' => 'Vacaciones',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],

            // Incapacidad (Sick Leave - CCSS covered)
            [
                'name' => 'Incapacidad por Enfermedad',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Incapacidad por Maternidad',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Incapacidad por Riesgo Laboral',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],

            // Licencias Especiales (Special Paid Leaves)
            [
                'name' => 'Licencia por Matrimonio',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Licencia por Paternidad',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Licencia por Duelo',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Licencia por Adopción',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],

            // Permisos (Permissions)
            [
                'name' => 'Permiso Médico',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Permiso Personal',
                'is_paid' => false,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Permiso por Estudio',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],

            // Ausencias sin goce de salario
            [
                'name' => 'Ausencia sin Goce de Salario',
                'is_paid' => false,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($absenceTypes as $absenceType) {
            AbsenceType::firstOrCreate(
                ['name' => $absenceType['name']],
                $absenceType
            );
        }

        $this->command->info('Absence types seeded successfully!');
    }
}
