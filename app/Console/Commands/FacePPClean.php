<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacePPClean extends Command
{
    protected $signature = 'facepp:clean {--force : Skip confirmation}';
    protected $description = 'Delete all Face++ FaceSets and clear cache';

    public function handle(): int
    {
        $this->warn('⚠️  WARNING: This will delete ALL FaceSets from Face++ API!');
        $this->newLine();

        // Get credentials
        $apiKey = config('facepp.api_key');
        $apiSecret = config('facepp.api_secret');
        $apiUrl = config('facepp.api_url');

        if (empty($apiKey) || empty($apiSecret)) {
            $this->error('❌ API credentials are not configured!');
            return self::FAILURE;
        }

        try {
            // Get all FaceSets
            $this->info('Fetching all FaceSets...');
            $response = Http::asForm()->post("{$apiUrl}/faceset/getfacesets", [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ]);

            $data = $response->json();

            if (isset($data['error_message'])) {
                $this->error('❌ Failed to fetch FaceSets: ' . $data['error_message']);
                return self::FAILURE;
            }

            $facesets = $data['facesets'] ?? [];
            $count = count($facesets);

            if ($count === 0) {
                $this->info('✅ No FaceSets found to delete.');
                $this->clearCache();
                return self::SUCCESS;
            }

            // Show FaceSets
            $this->info("Found {$count} FaceSet(s):");
            $this->newLine();

            $tableData = [];
            foreach ($facesets as $index => $faceset) {
                $tableData[] = [
                    $index + 1,
                    $faceset['faceset_token'] ?? 'N/A',
                    $faceset['outer_id'] ?? 'N/A',
                    $faceset['face_count'] ?? 0,
                ];
            }

            $this->table(
                ['#', 'FaceSet Token', 'Outer ID', 'Faces'],
                $tableData
            );

            $this->newLine();

            // Confirm deletion
            if (!$this->option('force')) {
                if (!$this->confirm("Do you want to delete all {$count} FaceSet(s)?", false)) {
                    $this->info('Operation cancelled.');
                    return self::SUCCESS;
                }
            }

            // Delete each FaceSet
            $this->info('Deleting FaceSets...');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();

            $deleted = 0;
            $failed = 0;

            foreach ($facesets as $faceset) {
                $token = $faceset['faceset_token'];

                try {
                    $deleteResponse = Http::asForm()->post("{$apiUrl}/faceset/delete", [
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret,
                        'faceset_token' => $token,
                        'check_empty' => 0, // Delete even if contains faces
                    ]);

                    $deleteData = $deleteResponse->json();

                    if (isset($deleteData['error_message'])) {
                        $this->newLine();
                        $this->error("Failed to delete {$token}: {$deleteData['error_message']}");
                        $failed++;
                        Log::error('Face++ Delete FaceSet Error', [
                            'token' => $token,
                            'error' => $deleteData['error_message'],
                        ]);
                    } else {
                        $deleted++;
                    }

                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("Exception deleting {$token}: {$e->getMessage()}");
                    $failed++;
                    Log::error('Face++ Delete FaceSet Exception', [
                        'token' => $token,
                        'message' => $e->getMessage(),
                    ]);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
            $this->newLine();

            // Summary
            if ($deleted > 0) {
                $this->info("✅ Successfully deleted {$deleted} FaceSet(s)");
            }
            if ($failed > 0) {
                $this->error("❌ Failed to delete {$failed} FaceSet(s)");
            }

            // Clear cache
            $this->clearCache();

            return $failed === 0 ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            Log::error('Face++ Clean Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    private function clearCache(): void
    {
        $this->info('Clearing cached FaceSet tokens...');

        $keysCleared = 0;

        if (Cache::has('facepp_faceset_token')) {
            Cache::forget('facepp_faceset_token');
            $keysCleared++;
        }

        if (Cache::has('facepp_faceset_outer_id')) {
            Cache::forget('facepp_faceset_outer_id');
            $keysCleared++;
        }

        if ($keysCleared > 0) {
            $this->info("✅ Cleared {$keysCleared} cache key(s)");
        } else {
            $this->info('No cache keys to clear');
        }
    }
}
