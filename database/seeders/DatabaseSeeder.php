<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────────
        $admin    = Role::create(['name' => 'admin',    'label' => 'Administrator']);
        $employee = Role::create(['name' => 'employee', 'label' => 'Employee']);

        // ── Users ──────────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@foodms.test',
            'password' => Hash::make('password'),
            'role_id'  => $admin->id,
        ]);

        User::create([
            'name'     => 'Jane Employee',
            'email'    => 'employee@foodms.test',
            'password' => Hash::make('password'),
            'role_id'  => $employee->id,
        ]);

        // ── Ingredients ────────────────────────────────────────────────────────
        $bun       = Ingredient::create(['name' => 'Burger Bun',       'unit' => 'pcs',   'quantity_in_stock' => 100, 'low_stock_threshold' => 20, 'cost_per_unit' => 8.00,  'category' => 'Bread']);
        $beef      = Ingredient::create(['name' => 'Beef Patty',       'unit' => 'pcs',   'quantity_in_stock' => 80,  'low_stock_threshold' => 15, 'cost_per_unit' => 45.00, 'category' => 'Meat']);
        $lettuce   = Ingredient::create(['name' => 'Lettuce',          'unit' => 'grams', 'quantity_in_stock' => 500, 'low_stock_threshold' => 100,'cost_per_unit' => 0.12,  'category' => 'Produce']);
        $tomato    = Ingredient::create(['name' => 'Tomato Slice',     'unit' => 'pcs',   'quantity_in_stock' => 60,  'low_stock_threshold' => 15, 'cost_per_unit' => 3.50,  'category' => 'Produce']);
        $cheese    = Ingredient::create(['name' => 'Cheese Slice',     'unit' => 'pcs',   'quantity_in_stock' => 5,   'low_stock_threshold' => 10, 'cost_per_unit' => 12.00, 'category' => 'Dairy']);  // intentionally low
        $ketchup   = Ingredient::create(['name' => 'Ketchup',          'unit' => 'ml',    'quantity_in_stock' => 800, 'low_stock_threshold' => 100,'cost_per_unit' => 0.05,  'category' => 'Condiment']);
        $fries     = Ingredient::create(['name' => 'Potato (frozen)',  'unit' => 'grams', 'quantity_in_stock' => 2000,'low_stock_threshold' => 300,'cost_per_unit' => 0.08,  'category' => 'Produce']);
        $cola      = Ingredient::create(['name' => 'Cola (355ml can)', 'unit' => 'pcs',   'quantity_in_stock' => 48,  'low_stock_threshold' => 12, 'cost_per_unit' => 22.00, 'category' => 'Beverage']);
        $chicken   = Ingredient::create(['name' => 'Chicken Fillet',   'unit' => 'pcs',   'quantity_in_stock' => 40,  'low_stock_threshold' => 10, 'cost_per_unit' => 38.00, 'category' => 'Meat']);
        $oil       = Ingredient::create(['name' => 'Cooking Oil',      'unit' => 'ml',    'quantity_in_stock' => 2000,'low_stock_threshold' => 200,'cost_per_unit' => 0.04,  'category' => 'Pantry']);

        // ── Menu Items with Recipes ────────────────────────────────────────────

        // Classic Burger
        $burger = MenuItem::create([
            'name'          => 'Classic Burger',
            'description'   => 'Juicy beef patty with fresh vegetables',
            'category'      => 'Burgers',
            'selling_price' => 189.00,
        ]);
        $burger->ingredients()->attach([
            $bun->id    => ['quantity_required' => 1],
            $beef->id   => ['quantity_required' => 1],
            $lettuce->id=> ['quantity_required' => 20],
            $tomato->id => ['quantity_required' => 2],
            $ketchup->id=> ['quantity_required' => 15],
        ]);
        $burger->load('ingredients');
        $burger->recalculateCost();

        // Cheeseburger
        $cheeseburger = MenuItem::create([
            'name'          => 'Cheeseburger',
            'description'   => 'Classic burger topped with melted cheese',
            'category'      => 'Burgers',
            'selling_price' => 219.00,
        ]);
        $cheeseburger->ingredients()->attach([
            $bun->id    => ['quantity_required' => 1],
            $beef->id   => ['quantity_required' => 1],
            $cheese->id => ['quantity_required' => 2],
            $lettuce->id=> ['quantity_required' => 20],
            $tomato->id => ['quantity_required' => 2],
            $ketchup->id=> ['quantity_required' => 15],
        ]);
        $cheeseburger->load('ingredients');
        $cheeseburger->recalculateCost();

        // Chicken Sandwich
        $sandwich = MenuItem::create([
            'name'          => 'Crispy Chicken Sandwich',
            'description'   => 'Golden fried chicken fillet on a toasted bun',
            'category'      => 'Sandwiches',
            'selling_price' => 179.00,
        ]);
        $sandwich->ingredients()->attach([
            $bun->id    => ['quantity_required' => 1],
            $chicken->id=> ['quantity_required' => 1],
            $lettuce->id=> ['quantity_required' => 15],
            $oil->id    => ['quantity_required' => 30],
            $ketchup->id=> ['quantity_required' => 10],
        ]);
        $sandwich->load('ingredients');
        $sandwich->recalculateCost();

        // Fries
        $friesItem = MenuItem::create([
            'name'          => 'French Fries',
            'description'   => 'Golden crispy fries with ketchup',
            'category'      => 'Sides',
            'selling_price' => 79.00,
        ]);
        $friesItem->ingredients()->attach([
            $fries->id  => ['quantity_required' => 150],
            $oil->id    => ['quantity_required' => 50],
            $ketchup->id=> ['quantity_required' => 20],
        ]);
        $friesItem->load('ingredients');
        $friesItem->recalculateCost();

        // Cola
        $colaItem = MenuItem::create([
            'name'          => 'Soft Drink',
            'description'   => 'Cold cola drink 355ml',
            'category'      => 'Drinks',
            'selling_price' => 55.00,
        ]);
        $colaItem->ingredients()->attach([
            $cola->id => ['quantity_required' => 1],
        ]);
        $colaItem->load('ingredients');
        $colaItem->recalculateCost();
    }
}