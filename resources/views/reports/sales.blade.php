@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('page-subtitle', 'Revenue, cost, and profit analysis')

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
    <a href="{{ route('reports.sales') }}" class="btn-ghost">Reset</a>
</form>

{{-- KPI Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="kpi-card sm:col-span-1">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Revenue</p>
        <p class="kpi-value text-slate-800 mt-1">₱{{ number_format($totals['revenue'], 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Cost</p>
        <p class="kpi-value text-slate-800 mt-1">₱{{ number_format($totals['cost'], 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Net Profit</p>
        <p class="kpi-value text-emerald-600 mt-1">₱{{ number_format($totals['profit'], 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Margin</p>
        <p class="kpi-value text-slate-800 mt-1">{{ $totals['margin'] }}%</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Orders</p>
        <p class="kpi-value text-slate-800 mt-1">{{ number_format($totals['orders']) }}</p>
    </div>
</div>

{{-- Chart --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-6">
    <h2 class="font-display font-bold text-slate-800 mb-4">Revenue vs Profit — Daily Breakdown</h2>
    <div class="h-64">
        <canvas id="reportChart"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Daily table --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-display font-bold text-slate-800">Daily Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="fms-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Date</th>
                        <th class="text-right">Orders</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Profit</th>
                        <th class="text-right">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daily as $row)
                    @php $margin = $row->revenue > 0 ? ($row->profit / $row->revenue * 100) : 0 @endphp
                    <tr>
                        <td class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                        <td class="text-right text-slate-600">{{ $row->orders }}</td>
                        <td class="text-right font-semibold text-slate-700">₱{{ number_format($row->revenue, 2) }}</td>
                        <td class="text-right text-slate-500">₱{{ number_format($row->cost, 2) }}</td>
                        <td class="text-right font-bold text-emerald-600">₱{{ number_format($row->profit, 2) }}</td>
                        <td class="text-right">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $margin >= 30 ? 'bg-green-100 text-green-600' : ($margin >= 15 ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-500') }}">
                                {{ round($margin, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-10 text-slate-400">No data for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment method breakdown --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-display font-bold text-slate-800 mb-4">By Payment Method</h2>
        <div class="space-y-3">
            @php $totalRev = $byPayment->sum('revenue') ?: 1 @endphp
            @foreach($byPayment as $pm)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium capitalize text-slate-700">{{ $pm->payment_method }}</span>
                    <span class="text-slate-500">{{ $pm->count }} orders</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full" style="background:var(--accent);
                            width:{{ ($pm->revenue / $totalRev) * 100 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 w-20 text-right">
                        ₱{{ number_format($pm->revenue, 0) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ctx2 = document.getElementById('reportChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Revenue',
                data: @json($chartRevenue),
                backgroundColor: 'rgba(249,115,22,0.7)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Profit',
                data: @json($chartProfit),
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderRadius: 6,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { font: { family: 'DM Sans', size: 12 }, color: '#64748b', boxWidth: 12, boxHeight: 12 }
            },
            tooltip: {
                backgroundColor: '#1e293b',
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2})
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 11 }, color: '#94a3b8' } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'DM Sans', size: 10 }, color: '#94a3b8',
                callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
@endpush