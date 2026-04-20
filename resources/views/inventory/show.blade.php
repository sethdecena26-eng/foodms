@extends('layouts.app')

@section('title', $ingredient->name)
@section('page-title', $ingredient->name)
@section('page-subtitle', $ingredient->category . ' · ' . $ingredient->unit)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ══════════════════════════════════════ LEFT ═══ --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Stock status card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="font-display font-bold text-xl text-slate-800">{{ $ingredient->name }}</h2>
                    <p class="text-sm text-slate-400 mt-0.5">{{ $ingredient->category ?? 'Uncategorised' }} · {{ $ingredient->unit }}</p>
                </div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('inventory.edit', $ingredient) }}" class="btn-ghost text-xs">Edit</a>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5">
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">In Stock</p>
                    <p class="text-2xl font-display font-bold {{ $ingredient->is_low_stock ? 'text-red-500' : 'text-slate-800' }}">
                        {{ number_format($ingredient->quantity_in_stock, 3) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $ingredient->unit }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Threshold</p>
                    <p class="text-2xl font-display font-bold text-slate-800">
                        {{ number_format($ingredient->low_stock_threshold, 3) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $ingredient->unit }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Cost / Unit</p>
                    <p class="text-2xl font-display font-bold text-slate-800">
                        ₱{{ number_format($ingredient->cost_per_unit, 4) }}
                    </p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Stock Value</p>
                    <p class="text-2xl font-display font-bold text-emerald-600">
                        ₱{{ number_format($ingredient->quantity_in_stock * $ingredient->cost_per_unit, 2) }}
                    </p>
                </div>
            </div>

            @if($ingredient->is_low_stock)
            <div class="mt-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700 font-medium">
                    Stock is at or below the low threshold. Consider restocking soon.
                </p>
            </div>
            @endif
        </div>

        {{-- Movement history --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-display font-bold text-slate-800">Movement History</h3>
                <p class="text-xs text-slate-400 mt-0.5">Immutable audit trail for all stock changes</p>
            </div>
            <div class="overflow-x-auto">
                <table class="fms-table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Date</th>
                            <th class="text-center">Type</th>
                            <th class="text-right">Before</th>
                            <th class="text-right">Change</th>
                            <th class="text-right">After</th>
                            <th class="text-left">By</th>
                            <th class="text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                        @php
                            $typeColors = [
                                'stock_in'       => 'badge-green',
                                'stock_out'      => 'badge-red',
                                'sale_deduction' => 'badge-amber',
                                'adjustment'     => 'badge-blue',
                            ];
                            $isIn = $m->quantity_changed > 0;
                        @endphp
                        <tr>
                            <td>
                                <p class="text-xs text-slate-600">{{ $m->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-400">{{ $m->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $typeColors[$m->type] ?? 'badge-slate' }}">
                                    {{ str_replace('_', ' ', ucfirst($m->type)) }}
                                </span>
                            </td>
                            <td class="text-right text-sm text-slate-500">{{ number_format($m->quantity_before, 3) }}</td>
                            <td class="text-right font-bold {{ $isIn ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $isIn ? '+' : '' }}{{ number_format($m->quantity_changed, 3) }}
                            </td>
                            <td class="text-right font-semibold text-slate-700">{{ number_format($m->quantity_after, 3) }}</td>
                            <td class="text-sm text-slate-500">{{ $m->user->name }}</td>
                            <td class="text-xs text-slate-400 max-w-[200px] truncate">{{ $m->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">No movements recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════ RIGHT ═══ --}}
    <div class="space-y-5">

        {{-- Stock In form --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Stock In</h3>
            <form method="POST" action="{{ route('inventory.stock-in', $ingredient) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="fms-label">Quantity ({{ $ingredient->unit }}) *</label>
                    <input type="number" name="quantity" step="0.001" min="0.001"
                           class="fms-input" required placeholder="e.g., 50">
                </div>
                <div>
                    <label class="fms-label">Notes</label>
                    <input type="text" name="notes" class="fms-input"
                           placeholder="e.g., Weekly delivery">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">
                    Add Stock
                </button>
            </form>
        </div>

        @if(auth()->user()->isAdmin())
        {{-- Manual adjust form --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-1">Manual Adjustment</h3>
            <p class="text-xs text-slate-400 mb-4">Set stock to an exact value (e.g., after physical count)</p>
            <form method="POST" action="{{ route('inventory.adjust', $ingredient) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="fms-label">New Quantity ({{ $ingredient->unit }}) *</label>
                    <input type="number" name="new_quantity" step="0.001" min="0"
                           value="{{ $ingredient->quantity_in_stock }}"
                           class="fms-input" required>
                </div>
                <div>
                    <label class="fms-label">Reason (required) *</label>
                    <input type="text" name="notes" class="fms-input" required
                           placeholder="e.g., Physical count correction">
                </div>
                <button type="submit"
                        onclick="return confirm('This will override the current stock value. Are you sure?')"
                        class="btn-ghost w-full text-center">
                    Apply Adjustment
                </button>
            </form>
        </div>
        @endif

        {{-- Used in menu items --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Used In Menu Items</h3>
            @forelse($ingredient->menuItems as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div>
                    <a href="{{ route('menu-items.show', $item) }}"
                       class="text-sm font-medium text-slate-700 hover:text-orange-500 transition-colors">
                        {{ $item->name }}
                    </a>
                    <p class="text-xs text-slate-400">
                        {{ $item->pivot->quantity_required }} {{ $ingredient->unit }} / serving
                    </p>
                </div>
                <span class="text-xs font-semibold text-slate-500">{{ $item->category }}</span>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Not used in any menu items yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

<style>
.fms-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:.35rem; }
.fms-input { width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; outline:none; transition:border-color .15s,box-shadow .15s; }
.fms-input:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgba(249,115,22,.1); }
</style>