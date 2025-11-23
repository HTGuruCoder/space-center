<?php

namespace App\Console\Commands;

use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FacePPSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facepp:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Face++ FaceSet for employee facial recognition';

    /**
     * Execute the console command.
     */
    public function handle(FaceRecognitionService $faceService): int
    {
        $this->info('Setting up Face++ FaceSet for employee facial recognition...');

        // Check if FaceSet already exists
        $existingToken = Cache::get('facepp_faceset_token');

        if ($existingToken) {
            $this->warn('FaceSet already exists in cache.');

            if (!$this->confirm('Do you want to verify the existing FaceSet?', true)) {
                return self::SUCCESS;
            }

            // Verify existing FaceSet
            $this->info('Verifying existing FaceSet...');
            $result = $faceService->getFaceSetDetail($existingToken);

            if ($result['success']) {
                $faceset = $result['faceset'];
                $this->info('FaceSet verified successfully!');
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Display Name', $faceset['display_name'] ?? 'N/A'],
                        ['Outer ID', $faceset['outer_id'] ?? 'N/A'],
                        ['FaceSet Token', $faceset['faceset_token'] ?? 'N/A'],
                        ['Face Count', $faceset['face_count'] ?? 0],
                    ]
                );
                return self::SUCCESS;
            } else {
                $this->error('Failed to verify existing FaceSet: ' . $result['message']);

                if (!$this->confirm('Do you want to create a new FaceSet?', true)) {
                    return self::FAILURE;
                }
            }
        }

        // Create new FaceSet
        $displayName = $this->ask('Enter a display name for the FaceSet', 'Employee Faces');
        $outerId = $this->ask('Enter an outer ID (optional, press Enter to skip)', 'employee_faceset');

        $this->info('Creating FaceSet...');

        $result = $faceService->createFaceSet(
            $displayName,
            $outerId ?: null,
            ['employees', 'authentication']
        );

        if (!$result['success']) {
            $this->error('Failed to create FaceSet: ' . $result['message']);
            return self::FAILURE;
        }

        $facesetToken = $result['faceset_token'];
        $outerId = $result['outer_id'];

        // Store FaceSet token in cache (permanent)
        Cache::forever('facepp_faceset_token', $facesetToken);

        if ($outerId) {
            Cache::forever('facepp_faceset_outer_id', $outerId);
        }

        $this->info('FaceSet created successfully!');
        $this->table(
            ['Property', 'Value'],
            [
                ['Display Name', $displayName],
                ['Outer ID', $outerId ?? 'N/A'],
                ['FaceSet Token', $facesetToken],
            ]
        );

        $this->info('FaceSet token stored in cache.');
        $this->newLine();
        $this->info('✓ Face++ setup completed successfully!');
        $this->info('You can now register employees with facial recognition.');

        return self::SUCCESS;
    }
}
