<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    protected $fillable = [
        'user_id', 'ingredient_id', 'waste_type',
        'item_name', 'quantity', 'unit',
        'cost_per_unit', 'waste_date',
        'reason', 'preventive_action',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'cost_per_unit'=> 'decimal:4',
        'waste_date'   => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }

    // Calculated since virtualAs may not work on all MySQL versions
    public function getTotalLossAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->cost_per_unit, 4);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('waste_date', [$from, $to]);
    }

    public static function wasteTypeLabels(): array
    {
        return [
            'expired'        => 'Expired',
            'spoilage'       => 'Spoilage',
            'overproduction' => 'Overproduction',
            'cooking_error'  => 'Cooking Error',
            'other'          => 'Other',
        ];
    }

    public static function wasteTypeColors(): array
    {
        return [
            'expired'        => 'bg-red-100 text-red-700',
            'spoilage'       => 'bg-orange-100 text-orange-700',
            'overproduction' => 'bg-amber-100 text-amber-700',
            'cooking_error'  => 'bg-purple-100 text-purple-700',
            'other'          => 'bg-slate-100 text-slate-600',
        ];
    }
}
