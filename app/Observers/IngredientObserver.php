<?php

namespace App\Observers;

use App\Models\Ingredient;

/**
 * Register in AppServiceProvider::boot():
 *   Ingredient::observe(IngredientObserver::class);
 *
 * When a cost_per_unit changes on any ingredient, every MenuItem that
 * uses that ingredient gets its computed_cost and margin recalculated.
 */
class IngredientObserver
{
    public function updated(Ingredient $ingredient): void
    {
        if (! $ingredient->wasChanged('cost_per_unit')) {
            return;
        }

        // Load all menu items that use this ingredient (with their full recipes)
        $ingredient->loadMissing('menuItems.ingredients');

        foreach ($ingredient->menuItems as $menuItem) {
            // Each menuItem already has ingredients loaded (including pivot)
            $menuItem->recalculateCost();
        }
    }
}