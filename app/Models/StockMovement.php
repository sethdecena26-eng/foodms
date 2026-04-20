<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    // Immutable — no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'ingredient_id',
        'user_id',
        'type',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'source_type',
        'source_id',
        'notes',
    ];

    protected $casts = [
        'quantity_before'  => 'decimal:3',
        'quantity_changed' => 'decimal:3',
        'quantity_after'   => 'decimal:3',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic link back to the source document (Order, etc.)
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}