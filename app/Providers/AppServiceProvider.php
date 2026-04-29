<?php

namespace App\Providers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Observers\IngredientObserver;
use App\Observers\MenuItemObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void {}

    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {

            \URL::forceScheme('https');}
        // Recalculate margin when selling_price changes on the item itself
        MenuItem::observe(MenuItemObserver::class);
        

        // Cascade cost recalculation to all menu items when an ingredient's
        // cost_per_unit is updated — fixes the gap where price changes on
        // ingredients weren't reflected in menu item computed costs
        Ingredient::observe(IngredientObserver::class);

        // Tailwind-compatible pagination links
        Paginator::useTailwind();
    }
}