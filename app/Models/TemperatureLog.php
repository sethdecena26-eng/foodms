<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemperatureLog extends Model
{
    protected $fillable = [
        'user_id', 'location', 'location_type',
        'temperature_celsius', 'min_safe_celsius', 'max_safe_celsius',
        'shift', 'log_date', 'log_time',
        'corrective_action', 'notes',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'min_safe_celsius'    => 'decimal:2',
        'max_safe_celsius'    => 'decimal:2',
        'log_date'            => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Safe range defaults per location type
    public static function safeRanges(): array
    {
        return [
            'fridge'   => ['min' => 1,  'max' => 5],   // 1–5°C
            'freezer'  => ['min' => -25,'max' => -18],  // -25 to -18°C
            'hot_hold' => ['min' => 63, 'max' => 100],  // above 63°C
            'other'    => ['min' => 1,  'max' => 5],
        ];
    }

    public function getIsWithinRangeAttribute(): bool
    {
        $temp = (float) $this->temperature_celsius;
        return $temp >= (float) $this->min_safe_celsius
            && $temp <= (float) $this->max_safe_celsius;
    }

    public function scopeOutOfRange($query)
    {
        return $query->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius');
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('log_date', $date);
    }
}
