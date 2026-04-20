<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('ingredients')->withCount('orderItems');

        // ── Fixed: apply category filter ──────────────────────────────
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $menuItems = $query->orderBy('category')
                           ->orderBy('name')
                           ->paginate(20)
                           ->withQueryString();

        $categories  = MenuItem::distinct()->pluck('category')->filter()->sort()->values();
        $totalItems  = MenuItem::count();
        $avgMargin   = MenuItem::where('selling_price', '>', 0)->avg('computed_profit_margin') ?? 0;
        $belowMargin = MenuItem::where('computed_profit_margin', '<', 30)
                               ->where('selling_price', '>', 0)
                               ->count();

        return view('menu-items.index', compact(
            'menuItems', 'categories', 'totalItems', 'avgMargin', 'belowMargin'
        ));
    }

    public function create()
    {
        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $categories  = MenuItem::distinct()->pluck('category')->filter()->sort()->values();

        return view('menu-items.create', compact('ingredients', 'categories'));
    }

    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $item = MenuItem::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'category'      => $data['category'],
            'selling_price' => $data['selling_price'],
            'is_available'  => $request->boolean('is_available', true),
            'image'         => $data['image'] ?? null,
        ]);

        if (!empty($data['recipe'])) {
            $this->syncRecipe($item, $data['recipe']);
        }

        return redirect()
            ->route('menu-items.show', $item)
            ->with('success', "'{$item->name}' has been created.");
    }

    // ── Fixed: was missing entirely ───────────────────────────────────
    public function show(MenuItem $menuItem)
    {
        $menuItem->load('ingredients'); // eager-load with pivot

        $salesData = $menuItem->orderItems()
            ->whereHas('order', fn($q) => $q->where('status', 'completed'))
            ->selectRaw('
                SUM(quantity)    AS total_sold,
                SUM(line_total)  AS total_revenue,
                SUM(line_profit) AS total_profit
            ')
            ->first();

        return view('menu-items.show', compact('menuItem', 'salesData'));
    }

    public function edit(MenuItem $menuItem)
    {
        $menuItem->load('ingredients');
        $ingredients = Ingredient::orderBy('category')->orderBy('name')->get();
        $categories  = MenuItem::distinct()->pluck('category')->filter()->sort()->values();

        return view('menu-items.edit', compact('menuItem', 'ingredients', 'categories'));
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($menuItem->image) Storage::disk('public')->delete($menuItem->image);
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem->update([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'category'      => $data['category'],
            'selling_price' => $data['selling_price'],
            'is_available'  => $request->boolean('is_available', true),
            'image'         => $data['image'] ?? $menuItem->image,
        ]);

        if (isset($data['recipe'])) {
            $this->syncRecipe($menuItem, $data['recipe']);
        }

        return redirect()
            ->route('menu-items.show', $menuItem)
            ->with('success', "'{$menuItem->name}' updated successfully.");
    }

    public function destroy(MenuItem $menuItem)
    {
        // Soft-delete only — use the Archive module to restore or permanently delete
        if ($menuItem->image) Storage::disk('public')->delete($menuItem->image);
        $menuItem->delete();

        return redirect()->route('menu-items.index')
                         ->with('success', "'{$menuItem->name}' archived. You can restore it from Archives.");
    }

    public function syncIngredients(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'recipe'                     => 'required|array|min:1',
            'recipe.*.ingredient_id'     => 'required|exists:ingredients,id',
            'recipe.*.quantity_required' => 'required|numeric|min:0.001',
        ]);

        $this->syncRecipe($menuItem, $data['recipe']);

        return back()->with('success', 'Recipe updated and cost recalculated.');
    }

    private function syncRecipe(MenuItem $item, array $rows): void
    {
        $pivot = collect($rows)->mapWithKeys(fn($row) => [
            (int)$row['ingredient_id'] => ['quantity_required' => $row['quantity_required']],
        ])->all();

        $item->ingredients()->sync($pivot);
        $item->load('ingredients');
        $item->recalculateCost();
    }
}