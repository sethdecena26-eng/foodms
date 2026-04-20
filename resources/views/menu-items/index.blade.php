@extends('layouts.app')

@section('title', 'Menu & Costing')
@section('page-title', 'Menu & Costing')
@section('page-subtitle', 'Manage items, build recipes, and analyze margins')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Total Items</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $totalItems }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Avg Margin</p>
        <p class="text-2xl font-display font-bold {{ $avgMargin >= 30 ? 'text-emerald-600' : 'text-amber-500' }} mt-1">
            {{ round($avgMargin, 1) }}%
        </p>
    </div>
    <div class="kpi-card {{ $belowMargin > 0 ? 'border-amber-200 bg-amber-50' : '' }}">
        <p class="text-xs {{ $belowMargin > 0 ? 'text-amber-500' : 'text-slate-400' }} font-semibold uppercase tracking-wide">Below 30% Margin</p>
        <p class="text-2xl font-display font-bold {{ $belowMargin > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-1">{{ $belowMargin }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Categories</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $categories->count() }}</p>
    </div>
</div>

{{-- Toolbar --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
        <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-display font-bold text-slate-800">All Menu Items</h2>
            {{-- Category filter pills --}}
            <a href="{{ route('menu-items.index') }}"
               class="text-xs px-3 py-1 rounded-full border transition-colors
                      {{ !request('category') ? 'border-orange-400 bg-orange-50 text-orange-600 font-semibold' : 'border-slate-200 text-slate-500 hover:border-slate-300' }}">
                All
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('menu-items.index', ['category' => $cat]) }}"
               class="text-xs px-3 py-1 rounded-full border transition-colors
                      {{ request('category') === $cat ? 'border-orange-400 bg-orange-50 text-orange-600 font-semibold' : 'border-slate-200 text-slate-500 hover:border-slate-300' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
        <a href="{{ route('menu-items.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Menu Item
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left w-8">#</th>
                    <th class="text-left">Item</th>
                    <th class="text-left">Category</th>
                    <th class="text-right">Selling Price</th>
                    <th class="text-right">Ingredient Cost</th>
                    <th class="text-right">Profit / Item</th>
                    <th class="text-center">Margin</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                @php
                    $margin     = (float) $item->computed_profit_margin;
                    $profit     = (float) $item->selling_price - (float) $item->computed_cost;
                    $marginColor = $margin >= 30 ? 'text-emerald-600 bg-green-100' : ($margin >= 15 ? 'text-amber-600 bg-amber-100' : 'text-red-600 bg-red-100');
                @endphp
                <tr>
                    <td class="text-slate-300 text-xs">{{ $menuItems->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3C7 3 3 7.58 3 11c0 1.93.78 3.68 2.05 4.97L6 20h12l.95-4.03C20.22 14.68 21 12.93 21 11c0-3.42-4-8-9-8z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('menu-items.show', $item) }}"
                                   class="font-semibold text-slate-700 hover:text-orange-500 transition-colors">
                                    {{ $item->name }}
                                </a>
                                <p class="text-xs text-slate-400">{{ $item->ingredients_count ?? $item->ingredients->count() }} ingredients</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">{{ $item->category }}</span>
                    </td>
                    <td class="text-right font-semibold text-slate-800">₱{{ number_format($item->selling_price, 2) }}</td>
                    <td class="text-right text-slate-500 text-sm">₱{{ number_format($item->computed_cost, 4) }}</td>
                    <td class="text-right font-semibold text-emerald-600">₱{{ number_format($profit, 2) }}</td>
                    <td class="text-center">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $marginColor }}">
                            {{ $margin }}%
                        </span>
                    </td>
                    <td class="text-center">
                        @if($item->is_available)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-600">Active</span>
                        @else
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Hidden</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('menu-items.show', $item) }}"
                               class="text-xs text-blue-500 hover:underline font-medium">View</a>
                            <a href="{{ route('menu-items.edit', $item) }}"
                               class="text-xs text-slate-400 hover:text-slate-600">Edit</a>
                            <form method="POST" action="{{ route('menu-items.destroy', $item) }}"
                                  onsubmit="return confirm('Remove {{ addslashes($item->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-12 text-slate-400">
                        No menu items yet.
                        <a href="{{ route('menu-items.create') }}" class="text-orange-500 hover:underline ml-1">Create your first →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-100">
        {{ $menuItems->links() }}
    </div>
</div>

@endsection