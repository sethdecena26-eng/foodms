@extends('layouts.app')
@section('title', 'Waste Report')
@section('page-title', 'Waste Report')
@section('page-subtitle', 'Financial impact of kitchen waste and expired items')

@section('content')

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
    <a href="{{ route('reports.waste') }}" class="btn-ghost">Reset</a>
</form>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Total Waste Loss</p>
        <p class="text-2xl font-display font-bold text-red-600 mt-1">₱{{ number_format($totalLoss, 2) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">% of Revenue</p>
        <p class="text-2xl font-display font-bold {{ $wastePct > 5 ? 'text-red-600' : 'text-slate-800' }} mt-1">{{ $wastePct }}%</p>
        <p class="text-xs text-slate-400 mt-1">Revenue: ₱{{ number_format($totalRevenue, 0) }}</p>
    </div>
    @foreach($byType->take(2) as $row)
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">{{ $wasteTypes[$row->waste_type] }}</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">₱{{ number_format($row->total_loss, 2) }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $row->count }} entries</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">
    {{-- Chart --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-display font-bold text-slate-800 mb-4">Daily Waste Loss</h3>
        <div class="h-56"><canvas id="wasteReportChart"></canvas></div>
    </div>

    {{-- By type --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-display font-bold text-slate-800 mb-4">Loss by Type</h3>
        @php $maxLoss = $byType->max('total_loss') ?: 1; @endphp
        <div class="space-y-3">
            @forelse($byType as $row)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-slate-700">{{ $wasteTypes[$row->waste_type] }}</span>
                    <span class="font-bold text-red-600">₱{{ number_format($row->total_loss, 2) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full bg-red-400" style="width:{{ ($row->total_loss/$maxLoss)*100 }}%"></div>
                    </div>
                    <span class="text-xs text-slate-400">{{ $row->count }}x</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">No waste data for this period.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Top wasted --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-display font-bold text-slate-800">Top Wasted Items</h3>
    </div>
    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">#</th>
                <th class="text-left">Item</th>
                <th class="text-right">Qty Wasted</th>
                <th class="text-right">Total Loss</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topWasted as $i => $item)
            <tr>
                <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                <td class="font-semibold text-slate-700">{{ $item->item_name }}</td>
                <td class="text-right text-slate-600">{{ number_format($item->qty, 0) }} {{ $item->unit }}</td>
                <td class="text-right font-bold text-red-600">₱{{ number_format($item->loss, 2) }}</td>
                <td class="text-right text-slate-400 text-sm">
                    {{ $totalLoss > 0 ? round(($item->loss / $totalLoss) * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-8 text-slate-400">No waste data for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
new Chart(document.getElementById('wasteReportChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Loss (₱)',
            data: @json($chartData),
            backgroundColor: 'rgba(239,68,68,0.6)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b',
            callbacks: { label: ctx => ' ₱' + ctx.parsed.y.toFixed(2) } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: v => '₱'+v } }
        }
    }
});
</script>
@endpush