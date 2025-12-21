<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacePPTest extends Command
{
    protected $signature = 'facepp:test';
    protected $description = 'Test Face++ API connection and credentials';

    public function handle(): int
    {
        $this->info('Testing Face++ API Connection...');
        $this->newLine();

        // Get credentials
        $apiKey = config('facepp.api_key');
        $apiSecret = config('facepp.api_secret');
        $apiUrl = config('facepp.api_url');

        // Display config
        $this->info('Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['API URL', $apiUrl],
                ['API Key', substr($apiKey, 0, 10) . '...'],
                ['API Secret', substr($apiSecret, 0, 10) . '...'],
            ]
        );
        $this->newLine();

        // Test 1: Check if credentials are set
        if (empty($apiKey) || empty($apiSecret)) {
            $this->error('❌ API credentials are not configured!');
            $this->info('Please check your .env file:');
            $this->line('FACEPP_API_KEY=your_key');
            $this->line('FACEPP_API_SECRET=your_secret');
            return self::FAILURE;
        }
        $this->info('✅ Credentials are configured');

        // Test 2: Test API connection with a simple request
        $this->info('Testing API connection...');
        try {
            $response = Http::timeout(10)->asForm()->post("{$apiUrl}/faceset/getfacesets", [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                $this->info('✅ API connection successful!');
                $this->newLine();

                if (isset($data['facesets'])) {
                    $count = count($data['facesets']);
                    $this->info("Found {$count} existing FaceSet(s):");

                    foreach ($data['facesets'] as $faceset) {
                        $this->line("  - Token: {$faceset['faceset_token']}");
                        $this->line("    Outer ID: " . ($faceset['outer_id'] ?? 'N/A'));
                    }
                } else {
                    $this->info('No existing FaceSets found.');
                }

                $this->newLine();
            } else {
                $this->error('❌ API request failed!');
                $this->line('Response: ' . json_encode($data, JSON_PRETTY_PRINT));

                if (isset($data['error_message'])) {
                    $this->error('Error: ' . $data['error_message']);

                    // Common errors
                    if (str_contains($data['error_message'], 'AUTHENTICATION_ERROR')) {
                        $this->warn('⚠️  Authentication failed. Check your API key and secret.');
                    } elseif (str_contains($data['error_message'], 'CONCURRENCY_LIMIT_EXCEEDED')) {
                        $this->warn('⚠️  API rate limit exceeded. Wait a moment and try again.');
                    } elseif (str_contains($data['error_message'], 'INVALID_API_KEY')) {
                        $this->warn('⚠️  Invalid API key. Check your FACEPP_API_KEY in .env');
                    }
                }

                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            Log::error('Face++ Test Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }

        // Test 3: Try to create a test FaceSet
        if ($this->confirm('Do you want to try creating a test FaceSet?', false)) {
            $this->info('Creating test FaceSet...');

            try {
                $response = Http::asForm()->post("{$apiUrl}/faceset/create", [
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                    'display_name' => 'Test FaceSet - ' . now()->format('Y-m-d H:i:s'),
                    'outer_id' => 'test_faceset_' . time(),
                ]);

                $data = $response->json();

                if (isset($data['error_message'])) {
                    $this->error('❌ Failed to create test FaceSet');
                    $this->line('Error: ' . $data['error_message']);
                    $this->newLine();

                    // Show full response for debugging
                    $this->warn('Full API Response:');
                    $this->line(json_encode($data, JSON_PRETTY_PRINT));

                } else {
                    $this->info('✅ Test FaceSet created successfully!');
                    $this->table(
                        ['Property', 'Value'],
                        [
                            ['FaceSet Token', $data['faceset_token'] ?? 'N/A'],
                            ['Outer ID', $data['outer_id'] ?? 'N/A'],
                        ]
                    );
                }

            } catch (\Exception $e) {
                $this->error('❌ Exception: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('✓ Test completed!');

        return self::SUCCESS;
    }
}
