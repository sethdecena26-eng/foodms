<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'selling_price',
        'image',
        'is_available',
        'computed_cost',
        'computed_profit_margin',
    ];

    protected $casts = [
        'selling_price'          => 'decimal:2',
        'computed_cost'          => 'decimal:4',
        'computed_profit_margin' => 'decimal:2',
        'is_available'           => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The recipe: ingredients with their required quantities.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_menu_item')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * All order line-items for this menu item.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Suggested selling price based on a 30% food-cost margin.
     * Formula: cost / 0.30  (food cost should be ≤30% of selling price)
     */
    public function getSuggestedPriceAttribute(): float
    {
        return $this->computed_cost > 0
            ? round($this->computed_cost / 0.30, 2)
            : 0.0;
    }

    /**
     * Actual profit per item sold.
     */
    public function getProfitPerItemAttribute(): float
    {
        return (float) $this->selling_price - (float) $this->computed_cost;
    }

    /**
     * Actual margin percentage.
     */
    public function getActualMarginAttribute(): float
    {
        if ($this->selling_price <= 0) return 0;
        return round(($this->profit_per_item / $this->selling_price) * 100, 2);
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Recalculate and persist the ingredient cost + margin.
     * Call this after recipe changes via the MenuItemObserver.
     */
    public function recalculateCost(): void
    {
        $cost = $this->ingredients->sum(function (Ingredient $ingredient) {
            return (float) $ingredient->cost_per_unit
                 * (float) $ingredient->pivot->quantity_required;
        });

        $margin = $this->selling_price > 0
            ? (($this->selling_price - $cost) / $this->selling_price) * 100
            : 0;

        $this->update([
            'computed_cost'          => $cost,
            'computed_profit_margin' => round($margin, 2),
        ]);
    }

    /**
     * Check whether all recipe ingredients have sufficient stock
     * to fulfil $quantity orders.
     *
     * Returns an array of deficit records, empty array means "OK".
     *
     * [['ingredient' => Ingredient, 'required' => float, 'available' => float], ...]
     */
    public function checkStock(int $quantity = 1): array
    {
        $deficits = [];

        foreach ($this->ingredients as $ingredient) {
            $required  = (float) $ingredient->pivot->quantity_required * $quantity;
            $available = (float) $ingredient->quantity_in_stock;

            if ($available < $required) {
                $deficits[] = [
                    'ingredient' => $ingredient,
                    'required'   => $required,
                    'available'  => $available,
                    'deficit'    => $required - $available,
                ];
            }
        }

        return $deficits;
    }

    /**
     * Return the ingredient cost snapshot for 1 serving.
     * Used when building order line-items so we capture cost at sale time.
     */
    public function currentCostSnapshot(): float
    {
        return $this->ingredients->sum(function (Ingredient $ingredient) {
            return (float) $ingredient->cost_per_unit
                 * (float) $ingredient->pivot->quantity_required;
        });
    }
}