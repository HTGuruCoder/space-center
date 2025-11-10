<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionSchedule extends Model
{
    use HasFactory, HasUuids, HasCreator, SoftDeletes;

    protected $fillable = [
        'position_id',
        'title',
        'description',
        'week_day',
        'start_time',
        'end_time',
        'created_by',
    ];

    /**
     * Get the position that owns the schedule.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
