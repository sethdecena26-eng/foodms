@extends('layouts.app')

@section('title', $menuItem->name)
@section('page-title', $menuItem->name)
@section('page-subtitle', $menuItem->category . ' · ' . ($menuItem->is_available ? 'Available on POS' : 'Hidden from POS'))

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ═══════════════════════════════ LEFT ═══ --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
            <div class="flex items-start gap-5">
                {{-- Image --}}
                <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                    @if($menuItem->image)
                        <img src="{{ asset('storage/' . $menuItem->image) }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 3C7 3 3 7.58 3 11c0 1.93.78 3.68 2.05 4.97L6 20h12l.95-4.03C20.22 14.68 21 12.93 21 11c0-3.42-4-8-9-8z"/>
                        </svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h2 class="font-display font-bold text-xl text-slate-800">{{ $menuItem->name }}</h2>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $menuItem->description ?? 'No description' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('menu-items.edit', $menuItem) }}" class="btn-ghost text-xs">Edit</a>
                        </div>
                    </div>

                    {{-- Price trio --}}
                    <div class="grid grid-cols-3 gap-3 mt-4">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Selling Price</p>
                            <p class="text-lg font-display font-bold text-slate-800 mt-0.5">₱{{ number_format($menuItem->selling_price, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Ingredient Cost</p>
                            <p class="text-lg font-display font-bold text-slate-800 mt-0.5">₱{{ number_format($menuItem->computed_cost, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Profit / Item</p>
                            <p class="text-lg font-display font-bold text-emerald-600 mt-0.5">₱{{ number_format($menuItem->selling_price - $menuItem->computed_cost, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recipe breakdown --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-display font-bold text-slate-800">Recipe Breakdown</h3>
                <p class="text-xs text-slate-400 mt-0.5">Ingredients required per 1 serving</p>
            </div>
            <table class="fms-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Ingredient</th>
                        <th class="text-center">Qty Required</th>
                        <th class="text-right">Cost / Unit</th>
                        <th class="text-right">Line Cost</th>
                        <th class="text-right">Cost %</th>
                        <th class="text-left">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCost = (float) $menuItem->computed_cost; @endphp
                    @forelse($menuItem->ingredients as $ing)
                    @php
                        $lineCost = (float) $ing->cost_per_unit * (float) $ing->pivot->quantity_required;
                        $costPct  = $totalCost > 0 ? ($lineCost / $totalCost) * 100 : 0;
                        $isLow    = $ing->quantity_in_stock <= $ing->low_stock_threshold;
                    @endphp
                    <tr>
                        <td>
                            <p class="font-medium text-slate-700">{{ $ing->name }}</p>
                            <p class="text-xs text-slate-400">{{ $ing->category }}</p>
                        </td>
                        <td class="text-center font-semibold text-slate-700">
                            {{ $ing->pivot->quantity_required }} {{ $ing->unit }}
                        </td>
                        <td class="text-right text-slate-500 text-sm">₱{{ number_format($ing->cost_per_unit, 2) }}</td>
                        <td class="text-right font-semibold text-slate-700">₱{{ number_format($lineCost, 2) }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full" style="background:var(--accent); width:{{ $costPct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400 w-8 text-right">{{ round($costPct, 0) }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($isLow)
                                <span class="low-stock-badge">Low</span>
                            @else
                                <span class="text-xs text-slate-400">{{ number_format($ing->quantity_in_stock, 0) }} {{ $ing->unit }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            No recipe defined.
                            <a href="{{ route('menu-items.edit', $menuItem) }}" class="text-orange-500 hover:underline ml-1">Add ingredients →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($menuItem->ingredients->count() > 0)
                <tfoot>
                    <tr class="bg-slate-50">
                        <td colspan="3" class="px-4 py-3 text-sm font-bold text-slate-700">Total per Serving</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">₱{{ number_format($menuItem->computed_cost, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════ RIGHT ═══ --}}
    <div class="space-y-5">

        {{-- Margin analysis card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Pricing Analysis</h3>

            @php
                $margin       = (float) $menuItem->computed_profit_margin;
                $suggested    = $menuItem->suggested_price;
                $sp           = (float) $menuItem->selling_price;
                $marginColor  = $margin >= 30 ? 'text-emerald-600' : ($margin >= 15 ? 'text-amber-500' : 'text-red-500');
            @endphp

            <div class="text-center py-4 mb-4 bg-slate-50 rounded-xl">
                <p class="text-xs text-slate-400 uppercase tracking-wide font-semibold mb-1">Profit Margin</p>
                <p class="text-4xl font-display font-bold {{ $marginColor }}">{{ $margin }}%</p>
                @if($margin >= 30)
                    <p class="text-xs text-emerald-500 mt-1 font-medium">✓ Healthy margin</p>
                @elseif($margin >= 15)
                    <p class="text-xs text-amber-500 mt-1 font-medium">⚠ Below recommended 30%</p>
                @else
                    <p class="text-xs text-red-500 mt-1 font-medium">✗ Critically low margin</p>
                @endif
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Suggested Price</span>
                    <span class="font-semibold {{ $sp >= $suggested ? 'text-emerald-600' : 'text-amber-600' }}">
                        ₱{{ number_format($suggested, 2) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Your Price</span>
                    <span class="font-semibold text-slate-800">₱{{ number_format($sp, 2) }}</span>
                </div>
                @if($sp < $suggested)
                <div class="flex justify-between pt-1 border-t border-slate-100">
                    <span class="text-amber-500 text-xs font-semibold">Price gap</span>
                    <span class="text-amber-600 text-xs font-bold">−₱{{ number_format($suggested - $sp, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Lifetime sales --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Lifetime Sales</h3>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Units Sold</span>
                    <span class="text-lg font-display font-bold text-slate-800">{{ number_format($salesData->total_sold ?? 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Total Revenue</span>
                    <span class="font-semibold text-slate-700">₱{{ number_format($salesData->total_revenue ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                    <span class="text-sm text-slate-500">Total Profit Generated</span>
                    <span class="font-bold text-emerald-600">₱{{ number_format($salesData->total_profit ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-2">
            <h3 class="font-display font-bold text-slate-800 mb-3">Actions</h3>
            <a href="{{ route('menu-items.edit', $menuItem) }}" class="btn-ghost w-full text-center block">
                Edit Item & Recipe
            </a>
            <form method="POST" action="{{ route('menu-items.destroy', $menuItem) }}"
                  onsubmit="return confirm('Archive {{ addslashes($menuItem->name) }}? It can be restored from Archives.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full py-2 px-4 rounded-lg text-sm font-semibold text-amber-600
                               border border-amber-200 hover:bg-amber-50 transition-colors">
                    Archive Item
                </button>
            </form>
        </div>
    </div>
</div>

@endsection