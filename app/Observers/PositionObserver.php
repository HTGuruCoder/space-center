<?php

namespace App\Observers;

use App\Models\Position;
use Illuminate\Support\Facades\Cache;

class PositionObserver
{
    /**
     * Clear positions cache when any modification occurs
     */
    private function clearCache(): void
    {
        Cache::forget('positions_select_options');
    }

    /**
     * Handle the Position "created" event.
     */
    public function created(Position $position): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Position "updated" event.
     */
    public function updated(Position $position): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Position "deleted" event.
     */
    public function deleted(Position $position): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Position "restored" event.
     */
    public function restored(Position $position): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Position "force deleted" event.
     */
    public function forceDeleted(Position $position): void
    {
        $this->clearCache();
    }
}
