@extends('layouts.app')

@section('title', 'Waste & Expiry')
@section('page-title', 'Waste & Expiry Management')
@section('page-subtitle', 'Track kitchen waste and calculate financial loss')

@section('content')

{{-- Date filter --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">From</label>
        <input type="date" name="from" value="{{ $from }}"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
    </div>
    <div>
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">To</label>
        <input type="date" name="to" value="{{ $to }}"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
    </div>
    <button type="submit" class="btn-primary">Apply</button>
    <a href="{{ route('waste.index') }}" class="btn-ghost">Reset</a>
</form>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card {{ $totalLoss > 0 ? 'border-red-100' : '' }}">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Total Loss</p>
        <p class="text-2xl font-display font-bold text-red-600 mt-1">₱{{ number_format($totalLoss, 2) }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $entryCount }} entries</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Expired Loss</p>
        <p class="text-2xl font-display font-bold text-orange-600 mt-1">₱{{ number_format($expiredLoss, 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Other Waste</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">₱{{ number_format($totalLoss - $expiredLoss, 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Avg Daily Loss</p>
        @php
            $days = max(1, \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to)) + 1);
        @endphp
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">₱{{ number_format($totalLoss / $days, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Log form --}}
    <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 p-5"
             x-data="wasteForm()">
            <h3 class="font-display font-bold text-slate-800 mb-4">Log Waste / Expiry</h3>

            <form method="POST" action="{{ route('waste.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="fms-label">Waste Type *</label>
                    <select name="waste_type" class="fms-input" required>
                        @foreach($wasteTypes as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fms-label">Ingredient (optional)</label>
                    <select name="ingredient_id" x-model="selectedIngredient"
                            @change="fillFromIngredient()" class="fms-input">
                        <option value="">— Select or type manually —</option>
                        @foreach($ingredients as $ing)
                        <option value="{{ $ing->id }}"
                                data-name="{{ $ing->name }}"
                                data-unit="{{ $ing->unit }}"
                                data-cost="{{ $ing->cost_per_unit }}">
                            {{ $ing->name }} ({{ $ing->unit }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="fms-label">Item Name *</label>
                    <input type="text" name="item_name" x-model="itemName"
                           class="fms-input" required placeholder="e.g., Beef Patty">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="fms-label">Quantity *</label>
                        <input type="number" name="quantity" x-model="qty"
                               step="0.001" min="0.001" class="fms-input" required>
                    </div>
                    <div>
                        <label class="fms-label">Unit *</label>
                        <input type="text" name="unit" x-model="unit"
                               class="fms-input" required placeholder="pcs, grams, ml">
                    </div>
                </div>

                <div>
                    <label class="fms-label">Cost Per Unit (₱) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₱</span>
                        <input type="number" name="cost_per_unit" x-model="costPerUnit"
                               step="0.0001" min="0" class="fms-input pl-7" required>
                    </div>
                </div>

                {{-- Live loss preview --}}
                <div class="px-3 py-2 bg-red-50 border border-red-100 rounded-lg"
                     x-show="qty > 0 && costPerUnit > 0">
                    <p class="text-xs text-red-600 font-semibold">
                        Estimated Loss:
                        <span class="text-lg font-display" x-text="'₱' + (qty * costPerUnit).toFixed(2)"></span>
                    </p>
                </div>

                <div>
                    <label class="fms-label">Waste Date *</label>
                    <input type="date" name="waste_date" value="{{ today()->format('Y-m-d') }}"
                           class="fms-input" required>
                </div>

                <div>
                    <label class="fms-label">Reason</label>
                    <textarea name="reason" rows="2" class="fms-input resize-none"
                              placeholder="What happened?"></textarea>
                </div>

                <div>
                    <label class="fms-label">Preventive Action</label>
                    <textarea name="preventive_action" rows="2" class="fms-input resize-none"
                              placeholder="How to prevent this next time?"></textarea>
                </div>

                <button type="submit" class="btn-primary w-full justify-center">Record Waste</button>
            </form>
        </div>
    </div>

    {{-- Right: breakdown + top wasted --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- 30-day trend chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">30-Day Waste Loss Trend</h3>
            <div class="h-48">
                <canvas id="wasteChart"></canvas>
            </div>
        </div>

        {{-- Breakdown by type --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Loss by Waste Type</h3>
            @php $maxLoss = $byType->max('total_loss') ?: 1; @endphp
            <div class="space-y-3">
                @forelse($byType as $row)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">{{ $wasteTypes[$row->waste_type] ?? $row->waste_type }}</span>
                        <span class="font-semibold text-red-600">₱{{ number_format($row->total_loss, 2) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full bg-red-400 transition-all"
                                 style="width: {{ ($row->total_loss / $maxLoss) * 100 }}%"></div>
                        </div>
                        <span class="text-xs text-slate-400">{{ $row->count }}x</span>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No waste recorded for this period.</p>
                @endforelse
            </div>
        </div>

        {{-- Top wasted ingredients --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Top Wasted Items</h3>
            <div class="space-y-2">
                @forelse($topWasted as $i => $item)
                <div class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 text-white"
                          style="background: var(--accent)">{{ $i + 1 }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-700">{{ $item->item_name }}</p>
                        <p class="text-xs text-slate-400">{{ number_format($item->qty, 0) }} {{ $item->unit }} wasted</p>
                    </div>
                    <span class="text-sm font-bold text-red-600">₱{{ number_format($item->loss, 2) }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No data for this period.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Log table --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-display font-bold text-slate-800">All Waste Entries</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Date</th>
                    <th class="text-left">Item</th>
                    <th class="text-center">Type</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Cost/Unit</th>
                    <th class="text-right">Total Loss</th>
                    <th class="text-left">Reason</th>
                    <th class="text-left">By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-sm text-slate-600">{{ $log->waste_date->format('M d, Y') }}</td>
                    <td>
                        <p class="font-semibold text-slate-700 text-sm">{{ $log->item_name }}</p>
                        <p class="text-xs text-slate-400">{{ $log->unit }}</p>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $wasteColors[$log->waste_type] ?? 'badge-slate' }}">
                            {{ $wasteTypes[$log->waste_type] ?? $log->waste_type }}
                        </span>
                    </td>
                    <td class="text-right font-semibold text-slate-700">{{ number_format($log->quantity, 0) }}</td>
                    <td class="text-right text-sm text-slate-500">₱{{ number_format($log->cost_per_unit, 2) }}</td>
                    <td class="text-right font-bold text-red-600">₱{{ number_format($log->total_loss, 2) }}</td>
                    <td class="text-xs text-slate-500 max-w-[160px] truncate">{{ $log->reason ?? '—' }}</td>
                    <td class="text-xs text-slate-400">{{ $log->user->name }}</td>
                    <td>
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('waste.destroy', $log) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-10 text-slate-400">No waste entries for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $logs->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
// Waste chart
const wCtx = document.getElementById('wasteChart').getContext('2d');
const wGrad = wCtx.createLinearGradient(0, 0, 0, 180);
wGrad.addColorStop(0, 'rgba(239,68,68,0.2)');
wGrad.addColorStop(1, 'rgba(239,68,68,0)');

new Chart(wCtx, {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Daily Loss (₱)',
            data: @json($chartData),
            borderColor: '#ef4444',
            backgroundColor: wGrad,
            borderWidth: 2,
            pointRadius: 3,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                callbacks: { label: ctx => ' ₱' + ctx.parsed.y.toFixed(2) }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8',
                callback: v => '₱' + v } }
        }
    }
});

// Alpine component for the form
function wasteForm() {
    const ingredients = {
        @foreach($ingredients as $ing)
        "{{ $ing->id }}": { name: "{{ $ing->name }}", unit: "{{ $ing->unit }}", cost: {{ (float)$ing->cost_per_unit }} },
        @endforeach
    };

    return {
        selectedIngredient: '',
        itemName: '',
        unit: '',
        costPerUnit: 0,
        qty: 0,
        fillFromIngredient() {
            const ing = ingredients[this.selectedIngredient];
            if (ing) {
                this.itemName    = ing.name;
                this.unit        = ing.unit;
                this.costPerUnit = ing.cost;
            }
        }
    };
}
</script>
@endpush

<style>
.fms-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:.35rem; }
.fms-input { width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; outline:none; transition:border-color .15s,box-shadow .15s; }
.fms-input:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgba(249,115,22,.1); }
</style>