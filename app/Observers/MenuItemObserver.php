<?php

namespace App\Observers;

use App\Models\MenuItem;

/**
 * Register in AppServiceProvider::boot():
 *   MenuItem::observe(MenuItemObserver::class);
 */
class MenuItemObserver
{
    /**
     * Recalculate cost whenever the selling price changes directly on the model.
     */
    public function updated(MenuItem $item): void
    {
        if ($item->wasChanged('selling_price')) {
            // Reload pivot data before recalculating
            $item->load('ingredients');
            $item->recalculateCost();
        }
    }
}