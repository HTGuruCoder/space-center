<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAllowedLocation extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'latitude',
        'longitude',
        'valid_from',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the allowed location.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if this location is currently valid (based on date range).
     *
     * @return bool
     */
    public function isCurrentlyValid(): bool
    {
        $today = now()->startOfDay();

        if ($this->valid_from && $today->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $today->isAfter($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if given coordinates are within radius of this allowed location.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusKm Default 0.5km (500 meters)
     * @return bool
     */
    public function isWithinRadius(float $latitude, float $longitude, float $radiusKm = 0.5): bool
    {
        if (!$this->isCurrentlyValid()) {
            return false;
        }

        if ($this->latitude === null || $this->longitude === null) {
            return false;
        }

        $distance = $this->haversineDistance(
            $this->latitude,
            $this->longitude,
            $latitude,
            $longitude
        );

        return $distance <= $radiusKm;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
