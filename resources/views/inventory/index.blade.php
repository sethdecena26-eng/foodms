@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory Intelligence')
@section('page-subtitle', 'Track raw ingredients and stock levels')

@section('content')

{{-- Stats bar --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Total Ingredients</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $ingredients->total() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Low Stock</p>
        <p class="text-2xl font-display font-bold text-red-500 mt-1">{{ $lowCount }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Out of Stock</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $outCount }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Est. Stock Value</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">₱{{ number_format($stockValue, 0) }}</p>
    </div>
</div>

{{-- Table card --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
        <h2 class="font-display font-bold text-slate-800">All Ingredients</h2>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('inventory.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Ingredient
            </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Ingredient</th>
                    <th class="text-left">Category</th>
                    <th class="text-right">In Stock</th>
                    <th class="text-right">Threshold</th>
                    <th class="text-right">Cost / Unit</th>
                    <th class="text-left">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingredients as $ingredient)
                @php
                    $pct = $ingredient->low_stock_threshold > 0
                        ? ($ingredient->quantity_in_stock / $ingredient->low_stock_threshold) * 100
                        : 100;
                    $isLow  = $ingredient->quantity_in_stock <= $ingredient->low_stock_threshold;
                    $isOut  = $ingredient->quantity_in_stock <= 0;
                @endphp
                <tr>
                    <td>
                        <p class="font-semibold text-slate-700">{{ $ingredient->name }}</p>
                        <p class="text-xs text-slate-400">{{ $ingredient->unit }}</p>
                    </td>
                    <td>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">
                            {{ $ingredient->category ?? '—' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <p class="font-semibold {{ $isOut ? 'text-red-500' : ($isLow ? 'text-amber-500' : 'text-slate-700') }}">
                            {{ number_format($ingredient->quantity_in_stock, 2) }}
                        </p>
                        {{-- Mini progress bar --}}
                        <div class="w-16 h-1 bg-slate-100 rounded-full ml-auto mt-1 overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                 style="width: {{ min(100, $pct) }}%; background: {{ $isOut ? '#ef4444' : ($isLow ? '#f59e0b' : '#22c55e') }}">
                            </div>
                        </div>
                    </td>
                    <td class="text-right text-sm text-slate-500">{{ number_format($ingredient->low_stock_threshold, 2) }}</td>
                    <td class="text-right text-sm text-slate-700">₱{{ number_format($ingredient->cost_per_unit, 4) }}</td>
                    <td>
                        @if($isOut)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">Out of Stock</span>
                        @elseif($isLow)
                            <span class="low-stock-badge">Low Stock</span>
                        @else
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-600">OK</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Stock In button --}}
                            <button onclick="openStockIn({{ $ingredient->id }}, '{{ $ingredient->name }}', '{{ $ingredient->unit }}')"
                                    class="text-xs font-semibold text-blue-600 hover:underline px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                                Stock In
                            </button>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('inventory.edit', $ingredient) }}"
                               class="text-xs text-slate-400 hover:text-slate-600 px-1">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-slate-400">
                        No ingredients found. <a href="{{ route('inventory.create') }}" class="text-orange-500 hover:underline">Add one →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-100">
        {{ $ingredients->links() }}
    </div>
</div>

{{-- ── Stock In Modal ──────────────────────────────────────────────────── --}}
<div id="stockInModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h3 class="font-display font-bold text-slate-800 text-lg mb-1">Stock In</h3>
        <p id="stockInName" class="text-sm text-slate-500 mb-4"></p>

        <form id="stockInForm" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">
                        Quantity to Add <span id="stockInUnit" class="normal-case font-normal text-slate-400"></span>
                    </label>
                    <input name="quantity" type="number" min="0.001" step="0.001" required
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm
                                  focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">Notes</label>
                    <input name="notes" type="text" placeholder="e.g., Delivery from supplier"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm
                                  focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeStockIn()"
                            class="btn-ghost flex-1">Cancel</button>
                    <button type="submit" class="btn-primary flex-1 justify-center">Confirm Stock In</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openStockIn(id, name, unit) {
    document.getElementById('stockInName').textContent = name;
    document.getElementById('stockInUnit').textContent = '(' + unit + ')';
    document.getElementById('stockInForm').action = '/inventory/' + id + '/stock-in';
    document.getElementById('stockInModal').classList.remove('hidden');
}
function closeStockIn() {
    document.getElementById('stockInModal').classList.add('hidden');
}
</script>
@endpush