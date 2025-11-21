<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FacePPExport extends Command
{
    protected $signature = 'facepp:export';

    protected $description = 'Export Face++ FaceSet configuration to .env format';

    public function handle(): int
    {
        $facesetToken = Cache::get('facepp_faceset_token');
        $outerId = Cache::get('facepp_faceset_outer_id');

        if (!$facesetToken) {
            $this->error('No FaceSet token found in cache.');
            $this->info('Run "php artisan facepp:setup" first.');
            return self::FAILURE;
        }

        $this->info('Face++ FaceSet Configuration:');
        $this->newLine();
        $this->line('Add these to your production .env file:');
        $this->newLine();
        $this->line('FACEPP_FACESET_TOKEN=' . $facesetToken);
        if ($outerId) {
            $this->line('FACEPP_FACESET_OUTER_ID=' . $outerId);
        }
        $this->newLine();

        $this->info('✓ Copy these values to your production environment.');
        $this->info('Then run "php artisan facepp:import" in production.');

        return self::SUCCESS;
    }
}
