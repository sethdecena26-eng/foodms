@extends('layouts.app')

@section('title', 'Ingredient Usage Report')
@section('page-title', 'Ingredient Usage')
@section('page-subtitle', 'Track consumption and the full stock audit trail')

@section('content')

{{-- Date filter --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">From</label>
        <input type="date" name="from" value="{{ $from }}"
               class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
    </div>
    <div>
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">To</label>
        <input type="date" name="to" value="{{ $to }}"
               class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
    </div>
    <button type="submit" class="btn-primary">Apply Filter</button>
    <a href="{{ route('reports.ingredient-usage') }}" class="btn-ghost">Reset</a>
</form>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">

    {{-- Usage summary table --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-display font-bold text-slate-800">Ingredient Consumption</h2>
            <p class="text-xs text-slate-400 mt-0.5">Total used via sales deductions</p>
        </div>
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Ingredient</th>
                    <th class="text-right">Total Used</th>
                    <th class="text-right">Total Cost</th>
                    <th class="text-right">Deductions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usage as $row)
                <tr>
                    <td>
                        <p class="font-medium text-slate-700">{{ $row->name }}</p>
                        <p class="text-xs text-slate-400">{{ $row->unit }}</p>
                    </td>
                    <td class="text-right font-semibold text-slate-700">
                        {{ number_format($row->total_used, 3) }}
                        <span class="text-xs text-slate-400 font-normal">{{ $row->unit }}</span>
                    </td>
                    <td class="text-right text-sm font-semibold text-red-500">
                        −₱{{ number_format($row->total_cost, 2) }}
                    </td>
                    <td class="text-right text-sm text-slate-400">{{ $row->deduction_count }}x</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-10 text-slate-400">No usage data for this period.</td>
                </tr>
                @endforelse
            </tbody>
            @if($usage->isNotEmpty())
            <tfoot>
                <tr class="bg-slate-50">
                    <td class="px-4 py-3 font-bold text-slate-700 text-sm">Total COGS</td>
                    <td></td>
                    <td class="px-4 py-3 text-right font-bold text-red-600">
                        −₱{{ number_format($usage->sum('total_cost'), 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Usage bar chart --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-display font-bold text-slate-800 mb-4">Cost by Ingredient</h2>
        <div class="h-72">
            <canvas id="usageChart"></canvas>
        </div>
    </div>
</div>

{{-- Full Audit Trail --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-display font-bold text-slate-800">Stock Audit Trail</h2>
        <p class="text-xs text-slate-400 mt-0.5">Every stock movement — immutable record</p>
    </div>

    <div class="overflow-x-auto">
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Date & Time</th>
                    <th class="text-left">Ingredient</th>
                    <th class="text-center">Type</th>
                    <th class="text-right">Before</th>
                    <th class="text-right">Change</th>
                    <th class="text-right">After</th>
                    <th class="text-left">By</th>
                    <th class="text-left">Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditTrail as $mov)
                @php
                    $isIn  = $mov->quantity_changed > 0;
                    $typeColors = [
                        'stock_in'        => 'bg-green-100 text-green-700',
                        'stock_out'       => 'bg-red-100 text-red-700',
                        'sale_deduction'  => 'bg-orange-100 text-orange-700',
                        'adjustment'      => 'bg-blue-100 text-blue-700',
                    ];
                    $typeColor = $typeColors[$mov->type] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <tr>
                    <td class="text-xs text-slate-400 whitespace-nowrap">
                        {{ $mov->created_at->format('M d, Y') }}<br>
                        <span class="text-slate-300">{{ $mov->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="font-medium text-slate-700 text-sm">{{ $mov->ingredient->name }}</td>
                    <td class="text-center">
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $typeColor }} whitespace-nowrap">
                            {{ str_replace('_', ' ', ucfirst($mov->type)) }}
                        </span>
                    </td>
                    <td class="text-right text-sm text-slate-500">{{ number_format($mov->quantity_before, 3) }}</td>
                    <td class="text-right font-bold {{ $isIn ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $isIn ? '+' : '' }}{{ number_format($mov->quantity_changed, 3) }}
                    </td>
                    <td class="text-right font-semibold text-slate-700">{{ number_format($mov->quantity_after, 3) }}</td>
                    <td class="text-sm text-slate-500">{{ $mov->user->name }}</td>
                    <td class="text-xs text-slate-400 max-w-xs truncate">{{ $mov->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-10 text-slate-400">No movements recorded for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-100">
        {{ $auditTrail->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
const usageLabels = @json($usage->pluck('name'));
const usageCosts  = @json($usage->pluck('total_cost')->map(fn($v) => round($v, 2)));

const palette = [
    '#f97316','#fb923c','#fdba74',
    '#22c55e','#4ade80','#86efac',
    '#3b82f6','#60a5fa','#93c5fd',
    '#a855f7','#c084fc',
];

const usageCtx = document.getElementById('usageChart').getContext('2d');
new Chart(usageCtx, {
    type: 'bar',
    data: {
        labels: usageLabels,
        datasets: [{
            label: 'Cost (₱)',
            data: usageCosts,
            backgroundColor: usageLabels.map((_, i) => palette[i % palette.length]),
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                callbacks: { label: ctx => ' ₱' + ctx.parsed.x.toFixed(2) }
            }
        },
        scales: {
            x: {
                grid: { color: '#f1f5f9' },
                ticks: { font: { family: 'DM Sans', size: 10 }, color: '#94a3b8',
                         callback: v => '₱' + v }
            },
            y: {
                grid: { display: false },
                ticks: { font: { family: 'DM Sans', size: 11 }, color: '#64748b' }
            }
        }
    }
});
</script>
@endpush