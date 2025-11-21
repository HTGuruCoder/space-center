<?php

namespace App\Console\Commands;

use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FacePPImport extends Command
{
    protected $signature = 'facepp:import';

    protected $description = 'Import Face++ FaceSet configuration from environment variables';

    public function handle(FaceRecognitionService $faceService): int
    {
        $facesetToken = env('FACEPP_FACESET_TOKEN');
        $outerId = env('FACEPP_FACESET_OUTER_ID');

        if (!$facesetToken) {
            $this->error('FACEPP_FACESET_TOKEN not found in .env file.');
            $this->newLine();
            $this->info('Add this to your .env:');
            $this->line('FACEPP_FACESET_TOKEN=your_token_here');
            if ($outerId) {
                $this->line('FACEPP_FACESET_OUTER_ID=your_outer_id_here');
            }
            return self::FAILURE;
        }

        $this->info('Verifying FaceSet with Face++ API...');

        // Verify the FaceSet exists
        $result = $faceService->getFaceSetDetail($facesetToken);

        if (!$result['success']) {
            $this->error('Failed to verify FaceSet: ' . $result['message']);
            $this->warn('The FaceSet token may be invalid or expired.');
            return self::FAILURE;
        }

        $faceset = $result['faceset'];

        // Store in cache
        Cache::forever('facepp_faceset_token', $facesetToken);
        if ($outerId) {
            Cache::forever('facepp_faceset_outer_id', $outerId);
        }

        $this->info('FaceSet imported successfully!');
        $this->table(
            ['Property', 'Value'],
            [
                ['Display Name', $faceset['display_name'] ?? 'N/A'],
                ['Outer ID', $faceset['outer_id'] ?? 'N/A'],
                ['FaceSet Token', $faceset['faceset_token'] ?? 'N/A'],
                ['Face Count', $faceset['face_count'] ?? 0],
            ]
        );

        $this->info('FaceSet configuration stored in cache.');
        $this->newLine();
        $this->info('✓ Face++ is ready to use in this environment!');

        return self::SUCCESS;
    }
}
