<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::orderByRaw('quantity_in_stock <= low_stock_threshold DESC')
                                  ->orderBy('name')
                                  ->paginate(25);

        $lowCount   = Ingredient::whereRaw('quantity_in_stock <= low_stock_threshold')->count();
        $outCount   = Ingredient::where('quantity_in_stock', '<=', 0)->count();
        $stockValue = Ingredient::selectRaw('SUM(quantity_in_stock * cost_per_unit) as val')->value('val') ?? 0;

        return view('inventory.index', compact('ingredients', 'lowCount', 'outCount', 'stockValue'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(StoreIngredientRequest $request)
    {
        Ingredient::create($request->validated());

        return redirect()->route('inventory.index')
                         ->with('success', 'Ingredient added successfully.');
    }

    // ── Fixed: was missing ────────────────────────────────────────────
    public function show(Ingredient $inventory)
    {
        $ingredient = $inventory;
        $ingredient->load('menuItems');

        $movements = StockMovement::with('user')
            ->where('ingredient_id', $ingredient->id)
            ->latest()
            ->paginate(30);

        return view('inventory.show', compact('ingredient', 'movements'));
    }

    public function edit(Ingredient $inventory)
    {
        return view('inventory.edit', ['ingredient' => $inventory]);
    }

    public function update(UpdateIngredientRequest $request, Ingredient $inventory)
    {
        $inventory->update($request->validated());

        // Cascade: recalculate cost for every menu item using this ingredient
        $inventory->menuItems->each(fn($item) => $item->recalculateCost());

        return redirect()->route('inventory.index')
                         ->with('success', 'Ingredient updated. Affected menu item costs recalculated.');
    }

    public function destroy(Ingredient $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Ingredient removed.');
    }

    public function stockIn(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
            'notes'    => 'nullable|string|max:300',
        ]);

        $ingredient->addStock(
            qty:    (float) $data['quantity'],
            userId: $request->user()->id,
            type:   'stock_in',
            notes:  $data['notes'] ?? 'Manual stock-in by ' . $request->user()->name
        );

        return redirect()->route('inventory.index')
                         ->with('success', "Added {$data['quantity']} {$ingredient->unit} to {$ingredient->name}.");
    }

    public function adjust(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'new_quantity' => 'required|numeric|min:0',
            'notes'        => 'required|string|max:300',
        ]);

        $before = (float) $ingredient->quantity_in_stock;
        $after  = (float) $data['new_quantity'];
        $diff   = $after - $before;

        $ingredient->update(['quantity_in_stock' => $after]);

        StockMovement::create([
            'ingredient_id'    => $ingredient->id,
            'user_id'          => $request->user()->id,
            'type'             => 'adjustment',
            'quantity_before'  => $before,
            'quantity_changed' => $diff,
            'quantity_after'   => $after,
            'notes'            => $data['notes'],
        ]);

        return redirect()->route('inventory.index')
                         ->with('success', "{$ingredient->name} adjusted to {$after} {$ingredient->unit}.");
    }
}