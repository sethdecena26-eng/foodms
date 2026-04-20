@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Your food business at a glance — ' . now()->format('F j, Y'))

@section('content')

{{-- ══════════════════════════ NO ROLE ASSIGNED — HOLDING SCREEN ═══ --}}
@if(auth()->user()->isPending())
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
         style="background:rgba(249,115,22,.12); border:1px solid rgba(249,115,22,.2)">
        <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>

    <h2 class="font-display font-bold text-2xl text-slate-800 mb-2">Account Pending Approval</h2>
    <p class="text-slate-500 max-w-sm leading-relaxed mb-6">
        Your account has been created but hasn't been assigned a role yet.
        Please contact your system administrator to activate your access.
    </p>

    <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 text-left max-w-sm w-full space-y-2">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Your Account Details</p>
        <div class="flex justify-between text-sm">
            <span class="text-slate-500">Name</span>
            <span class="font-semibold text-slate-700">{{ auth()->user()->name }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-slate-500">Email</span>
            <span class="font-semibold text-slate-700">{{ auth()->user()->email }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-slate-500">Role</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600">Not assigned</span>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit"
                class="text-sm text-slate-400 hover:text-red-500 transition-colors font-medium">
            Sign out
        </button>
    </form>
</div>

{{-- Stop here — don't render KPIs for a pending user --}}
@else

{{-- ═══════════════════════════════════════ KPI CARDS ═══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Today's Revenue --}}
    <div class="kpi-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Revenue</p>
                <p class="kpi-value text-slate-800 mt-1">₱{{ number_format($todayStats->revenue ?? 0, 2) }}</p>
            </div>
            <div class="kpi-icon" style="background:#fff7ed">
                <svg class="w-5 h-5" style="color:var(--accent)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if($revenueChange !== null)
        <p class="text-xs mt-3 {{ $revenueChange >= 0 ? 'text-green-500' : 'text-red-500' }}">
            {{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs(round($revenueChange, 1)) }}% vs yesterday
        </p>
        @endif
    </div>

    {{-- Today's Profit --}}
    <div class="kpi-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Net Profit</p>
                <p class="kpi-value text-emerald-600 mt-1">₱{{ number_format($todayStats->profit ?? 0, 2) }}</p>
            </div>
            <div class="kpi-icon" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        @php
            $margin = ($todayStats->revenue ?? 0) > 0
                ? (($todayStats->profit / $todayStats->revenue) * 100)
                : 0;
        @endphp
        <p class="text-xs mt-3 text-slate-400">{{ round($margin, 1) }}% profit margin today</p>
    </div>

    {{-- Today's Orders --}}
    <div class="kpi-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Orders Today</p>
                <p class="kpi-value text-slate-800 mt-1">{{ number_format($todayStats->order_count ?? 0) }}</p>
            </div>
            <div class="kpi-icon" style="background:#eff6ff">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="text-xs mt-3 text-slate-400">
            Avg ₱{{ $todayStats->order_count > 0 ? number_format($todayStats->revenue / $todayStats->order_count, 2) : '0.00' }} / order
        </p>
    </div>

    {{-- Low Stock --}}
    <div class="kpi-card {{ $lowStockCount > 0 ? 'border-red-200 bg-red-50' : '' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold {{ $lowStockCount > 0 ? 'text-red-400' : 'text-slate-400' }} uppercase tracking-wider">
                    Low Stock Items
                </p>
                <p class="kpi-value {{ $lowStockCount > 0 ? 'text-red-600' : 'text-slate-800' }} mt-1">
                    {{ $lowStockCount }}
                </p>
            </div>
            <div class="kpi-icon {{ $lowStockCount > 0 ? '' : '' }}" style="background:{{ $lowStockCount > 0 ? '#fef2f2' : '#f8fafc' }}">
                <svg class="w-5 h-5 {{ $lowStockCount > 0 ? 'text-red-500' : 'text-slate-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('inventory.index') }}" class="text-xs mt-3 inline-block
           {{ $lowStockCount > 0 ? 'text-red-500 font-semibold hover:underline' : 'text-slate-400' }}">
            {{ $lowStockCount > 0 ? 'View & restock →' : 'All levels normal ✓' }}
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════ CHARTS + TOP ITEMS ═══ --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">

    {{-- 7-day Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-display font-bold text-slate-800">7-Day Sales Trend</h2>
                <p class="text-xs text-slate-400 mt-0.5">Revenue vs Net Profit</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 rounded" style="background:var(--accent); display:inline-block"></span>
                    Revenue
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 rounded bg-emerald-500 inline-block"></span>
                    Profit
                </span>
            </div>
        </div>
        <div class="relative h-56">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- Top Selling Items --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-display font-bold text-slate-800 mb-1">Top Sellers</h2>
        <p class="text-xs text-slate-400 mb-4">Last 30 days by quantity</p>

        <div class="space-y-3">
            @forelse($topItems as $i => $item)
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                    {{ $i === 0 ? 'text-white' : 'text-slate-400 bg-slate-100' }}"
                    style="{{ $i === 0 ? 'background:var(--accent)' : '' }}">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $item->name }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full" style="background:var(--accent);
                                width:{{ $topItems->max('total_qty') > 0 ? ($item->total_qty / $topItems->max('total_qty') * 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-xs text-slate-400 flex-shrink-0">{{ $item->total_qty }}x</span>
                    </div>
                </div>
                <span class="text-xs font-semibold text-emerald-600 flex-shrink-0">
                    ₱{{ number_format($item->total_profit, 0) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-6">No sales data yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════ MONTHLY SUMMARY ═══ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-100 px-5 py-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Month Revenue</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">
            ₱{{ number_format($monthStats->revenue ?? 0, 0) }}
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ now()->format('F Y') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 px-5 py-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Month Profit</p>
        <p class="text-2xl font-display font-bold text-emerald-600 mt-1">
            ₱{{ number_format($monthStats->profit ?? 0, 0) }}
        </p>
        @php $mMargin = ($monthStats->revenue ?? 0) > 0 ? ($monthStats->profit / $monthStats->revenue * 100) : 0 @endphp
        <p class="text-xs text-slate-400 mt-1">{{ round($mMargin, 1) }}% margin</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 px-5 py-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Month Orders</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">
            {{ number_format($monthStats->order_count ?? 0) }}
        </p>
        <p class="text-xs text-slate-400 mt-1">completed transactions</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('salesChart').getContext('2d');

const gradient = ctx.createLinearGradient(0, 0, 0, 200);
gradient.addColorStop(0, 'rgba(249,115,22,0.18)');
gradient.addColorStop(1, 'rgba(249,115,22,0)');

const profitGradient = ctx.createLinearGradient(0, 0, 0, 200);
profitGradient.addColorStop(0, 'rgba(34,197,94,0.12)');
profitGradient.addColorStop(1, 'rgba(34,197,94,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($trendLabels),
        datasets: [
            {
                label: 'Revenue',
                data: @json($trendRevenue),
                borderColor: '#f97316',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#f97316',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Profit',
                data: @json($trendProfit),
                borderColor: '#22c55e',
                backgroundColor: profitGradient,
                borderWidth: 2,
                pointBackgroundColor: '#22c55e',
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.4,
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                titleFont: { family: 'DM Sans', size: 11 },
                bodyFont:  { family: 'DM Sans', size: 12 },
                padding: 10,
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { family: 'DM Sans', size: 11 }, color: '#94a3b8' }
            },
            y: {
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: {
                    font: { family: 'DM Sans', size: 10 }, color: '#94a3b8',
                    callback: v => '₱' + v.toLocaleString()
                }
            }
        }
    }
});
</script>
@endpush

@endif {{-- end @if(auth()->user()->isPending()) --}}