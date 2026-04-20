<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'quantity_in_stock',
        'low_stock_threshold',
        'cost_per_unit',
        'category',
    ];

    protected $casts = [
        'quantity_in_stock'   => 'decimal:3',
        'low_stock_threshold' => 'decimal:3',
        'cost_per_unit'       => 'decimal:4',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * All menu items that use this ingredient (via recipe pivot).
     */
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'ingredient_menu_item')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * Every stock movement (audit trail) for this ingredient.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * True when stock is at or below the low-stock threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_in_stock <= $this->low_stock_threshold;
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Deduct stock and record the movement.
     * Called by the Order model during completion.
     *
     * @throws \App\Exceptions\InsufficientStockException
     */
    public function deductStock(
        float  $qty,
        int    $userId,
        string $type = 'sale_deduction',
        ?Model $source = null,
        string $notes = ''
    ): void {
        if ($this->quantity_in_stock < $qty) {
            throw new \App\Exceptions\InsufficientStockException(
                "Insufficient stock for [{$this->name}]. "
                . "Required: {$qty} {$this->unit}, "
                . "Available: {$this->quantity_in_stock} {$this->unit}."
            );
        }

        $before = (float) $this->quantity_in_stock;
        $after  = $before - $qty;

        $this->decrement('quantity_in_stock', $qty);

        $this->recordMovement($userId, $type, $before, -$qty, $after, $source, $notes);
    }

    /**
     * Add stock (stock-in / manual adjustment) and record the movement.
     */
    public function addStock(
        float  $qty,
        int    $userId,
        string $type = 'stock_in',
        ?Model $source = null,
        string $notes = ''
    ): void {
        $before = (float) $this->quantity_in_stock;
        $after  = $before + $qty;

        $this->increment('quantity_in_stock', $qty);

        $this->recordMovement($userId, $type, $before, $qty, $after, $source, $notes);
    }

    /**
     * Write an immutable stock_movements row.
     */
    private function recordMovement(
        int    $userId,
        string $type,
        float  $before,
        float  $changed,
        float  $after,
        ?Model $source,
        string $notes
    ): void {
        $data = [
            'ingredient_id'   => $this->id,
            'user_id'         => $userId,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_changed'=> $changed,
            'quantity_after'  => $after,
            'notes'           => $notes,
        ];

        if ($source) {
            $data['source_type'] = $source->getMorphClass();
            $data['source_id']   = $source->getKey();
        }

        StockMovement::create($data);
    }
}