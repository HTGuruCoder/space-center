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
            [
                'name' => 'Lunch Break',
                'is_paid' => true,
                'is_break' => true,
                'requires_validation' => false,
                'max_per_day' => 2, // Max 2 lunch breaks per day
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Sick Leave',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null, // No daily limit
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Vacation',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Personal Day',
                'is_paid' => true,
                'is_break' => false,
                'requires_validation' => true,
                'max_per_day' => null,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Unpaid Leave',
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
