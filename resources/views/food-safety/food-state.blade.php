@extends('layouts.app')
@section('title', 'Food Safety Report')
@section('page-title', 'Food Safety Report')
@section('page-subtitle', 'Temperature compliance and HACCP checklist history')

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
    <a href="{{ route('reports.food-safety') }}" class="btn-ghost">Reset</a>
</form>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card {{ $tempOutRange > 0 ? 'border-red-200 bg-red-50' : '' }}">
        <p class="text-xs {{ $tempOutRange > 0 ? 'text-red-400' : 'text-slate-400' }} font-semibold uppercase tracking-wide">Temp Compliance</p>
        <p class="text-2xl font-display font-bold {{ $tempCompliance >= 95 ? 'text-emerald-600' : ($tempCompliance >= 80 ? 'text-amber-500' : 'text-red-600') }} mt-1">
            {{ $tempCompliance }}%
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ $tempTotal }} total logs</p>
    </div>
    <div class="kpi-card {{ $tempOutRange > 0 ? 'border-red-200' : '' }}">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Out of Range</p>
        <p class="text-2xl font-display font-bold {{ $tempOutRange > 0 ? 'text-red-600' : 'text-slate-800' }} mt-1">{{ $tempOutRange }}</p>
        <p class="text-xs text-slate-400 mt-1">temperature alerts</p>
    </div>
    <div class="kpi-card {{ $haccpRate < 80 ? 'border-amber-200 bg-amber-50' : '' }}">
        <p class="text-xs {{ $haccpRate < 80 ? 'text-amber-500' : 'text-slate-400' }} font-semibold uppercase tracking-wide">HACCP Rate</p>
        <p class="text-2xl font-display font-bold {{ $haccpRate >= 90 ? 'text-emerald-600' : ($haccpRate >= 70 ? 'text-amber-500' : 'text-red-600') }} mt-1">
            {{ $haccpRate }}%
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ $haccpCompleted }}/{{ $haccpPossible }} shifts</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Missed Shifts</p>
        <p class="text-2xl font-display font-bold {{ $haccpMissed > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-1">{{ $haccpMissed }}</p>
        <p class="text-xs text-slate-400 mt-1">checklists not completed</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">

    {{-- Out of range logs --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display font-bold text-slate-800">Temperature Alerts</h3>
            <p class="text-xs text-slate-400 mt-0.5">Readings outside safe range</p>
        </div>
        <table class="fms-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Date</th>
                    <th class="text-left">Location</th>
                    <th class="text-right">Temp</th>
                    <th class="text-left">Action Taken</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outOfRangeLogs as $log)
                <tr class="bg-red-50">
                    <td class="text-xs text-slate-600">{{ $log->log_date->format('M d') }}</td>
                    <td class="font-medium text-slate-700 text-sm">{{ $log->location }}</td>
                    <td class="text-right font-bold text-red-600">{{ $log->temperature_celsius }}°C</td>
                    <td class="text-xs text-slate-500 max-w-[150px] truncate">{{ $log->corrective_action ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-emerald-600 font-medium">✓ No alerts in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- By location summary --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-display font-bold text-slate-800 mb-4">Compliance by Location</h3>
        <div class="space-y-3">
            @forelse($byLocation as $loc)
            @php $locRate = $loc->total > 0 ? round((($loc->total - $loc->alerts) / $loc->total) * 100) : 100; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-slate-700">{{ $loc->location }}</span>
                    <span class="text-xs {{ $locRate < 90 ? 'text-red-500 font-bold' : 'text-slate-400' }}">
                        {{ $locRate }}% — avg {{ round($loc->avg_temp, 1) }}°C
                    </span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full {{ $locRate >= 95 ? 'bg-emerald-400' : ($locRate >= 80 ? 'bg-amber-400' : 'bg-red-400') }}"
                         style="width:{{ $locRate }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">No temperature logs for this period.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- HACCP history --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-display font-bold text-slate-800">HACCP Checklist History</h3>
    </div>
    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">Date</th>
                <th class="text-center">Opening</th>
                <th class="text-center">Closing</th>
                <th class="text-center">Compliance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($haccpHistory as $date => $checklists)
            @php
                $open  = $checklists->firstWhere('shift_type','opening');
                $close = $checklists->firstWhere('shift_type','closing');
            @endphp
            <tr>
                <td class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($date)->format('D, M d, Y') }}</td>
                <td class="text-center">
                    @if($open) <span class="badge badge-green">✓ {{ $open->user->name }}</span>
                    @else <span class="badge badge-red">Missed</span> @endif
                </td>
                <td class="text-center">
                    @if($close) <span class="badge badge-green">✓ {{ $close->user->name }}</span>
                    @else <span class="badge badge-slate">Missed</span> @endif
                </td>
                <td class="text-center">
                    @if($open && $close) <span class="badge badge-green">100%</span>
                    @elseif($open || $close) <span class="badge badge-amber">50%</span>
                    @else <span class="badge badge-red">0%</span> @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center py-8 text-slate-400">No checklist history for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection