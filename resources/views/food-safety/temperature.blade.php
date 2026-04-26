@extends('layouts.app')

@section('title', 'Temperature Logs')
@section('page-title', 'Temperature Logs')
@section('page-subtitle', 'Daily fridge, freezer & hot-hold monitoring')

@section('content')

{{-- Date picker + KPIs --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm font-medium text-slate-600">Date:</label>
        <input type="date" name="date" value="{{ $date }}"
               onchange="this.form.submit()"
               class="fms-input py-2 w-44">
    </form>
    <div class="flex gap-3">
        <div class="kpi-card py-3 px-4">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Logs Today</p>
            <p class="text-xl font-display font-bold text-slate-800 mt-0.5">{{ $logs->count() }}</p>
        </div>
        <div class="kpi-card py-3 px-4 {{ $outOfRangeCount > 0 ? 'border-red-200 bg-red-50' : '' }}">
            <p class="text-xs {{ $outOfRangeCount > 0 ? 'text-red-400' : 'text-slate-400' }} font-semibold uppercase tracking-wide">Out of Range</p>
            <p class="text-xl font-display font-bold {{ $outOfRangeCount > 0 ? 'text-red-600' : 'text-slate-800' }} mt-0.5">{{ $outOfRangeCount }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ══════════════════ LEFT: Log form ══════════════════ --}}
    <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Record Temperature</h3>

            <form method="POST" action="{{ route('food-safety.temperature.store') }}" class="space-y-3"
                  x-data="tempForm()">
                @csrf

                <div>
                    <label class="fms-label">Location *</label>
                    <input type="text" name="location" required class="fms-input"
                           placeholder="e.g., Walk-in Fridge, Freezer 1"
                           list="location-suggestions">
                    <datalist id="location-suggestions">
                        <option value="Walk-in Fridge">
                        <option value="Prep Fridge">
                        <option value="Display Fridge">
                        <option value="Main Freezer">
                        <option value="Chest Freezer">
                        <option value="Hot Hold Counter">
                    </datalist>
                </div>

                <div>
                    <label class="fms-label">Type *</label>
                    <select name="location_type" x-model="locationType"
                            @change="setDefaults()" class="fms-input" required>
                        <option value="fridge"> Fridge</option>
                        <option value="freezer"> Freezer</option>
                        <option value="hot_hold"> Hot Hold</option>
                        <option value="other"> Other</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="fms-label">Min Safe (°C) *</label>
                        <input type="number" name="min_safe_celsius" step="0.1"
                               x-model="minSafe" class="fms-input" required>
                    </div>
                    <div>
                        <label class="fms-label">Max Safe (°C) *</label>
                        <input type="number" name="max_safe_celsius" step="0.1"
                               x-model="maxSafe" class="fms-input" required>
                    </div>
                </div>

                <div>
                    <label class="fms-label">Recorded Temperature (°C) *</label>
                    <div class="relative">
                        <input type="number" name="temperature_celsius" step="0.1"
                               x-model="temp" class="fms-input pr-16" required
                               placeholder="0.0">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold"
                              :class="isWithinRange ? 'text-emerald-500' : 'text-red-500'"
                              x-text="isWithinRange ? '✓ OK' : '✗ ALERT'">
                        </span>
                    </div>
                    <p class="text-xs mt-1" :class="isWithinRange ? 'text-emerald-500' : 'text-red-500'"
                       x-show="temp !== ''">
                        Safe range: <span x-text="minSafe"></span>°C to <span x-text="maxSafe"></span>°C
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="fms-label">Shift *</label>
                        <select name="shift" class="fms-input" required>
                            <option value="opening">Opening</option>
                            <option value="midday">Midday</option>
                            <option value="closing">Closing</option>
                        </select>
                    </div>
                    <div>
                        <label class="fms-label">Log Date *</label>
                        <input type="date" name="log_date" value="{{ $date }}" class="fms-input" required>
                    </div>
                </div>

                <div>
                    <label class="fms-label">Log Time *</label>
                    <input type="time" name="log_time" value="{{ now()->format('H:i') }}" class="fms-input" required>
                </div>

                <div x-show="!isWithinRange && temp !== ''">
                    <label class="fms-label text-red-500">Corrective Action Taken *</label>
                    <textarea name="corrective_action" rows="2" class="fms-input resize-none"
                              :required="!isWithinRange"
                              placeholder="e.g., Adjusted thermostat, moved items, called maintenance..."></textarea>
                </div>

                <div>
                    <label class="fms-label">Notes</label>
                    <input type="text" name="notes" class="fms-input" placeholder="Optional">
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    Save Log
                </button>
            </form>
        </div>
    </div>

    {{-- ══════════════════ RIGHT: Today's logs ══════════════════ --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Today's logs table --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-display font-bold text-slate-800">
                    Logs for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                </h3>
            </div>
            <table class="fms-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Location</th>
                        <th class="text-center">Shift</th>
                        <th class="text-right">Temp (°C)</th>
                        <th class="text-center">Safe Range</th>
                        <th class="text-center">Status</th>
                        <th class="text-left">Corrective Action</th>
                        <th class="text-left">By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="{{ !$log->is_within_range ? 'bg-red-50' : '' }}">
                        <td>
                            <p class="font-semibold text-slate-700">{{ $log->location }}</p>
                            <p class="text-xs text-slate-400 capitalize">{{ str_replace('_',' ',$log->location_type) }}</p>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $log->shift === 'opening' ? 'badge-blue' : ($log->shift === 'closing' ? 'badge-slate' : 'badge-amber') }}">
                                {{ ucfirst($log->shift) }}
                            </span>
                        </td>
                        <td class="text-right font-bold text-lg {{ !$log->is_within_range ? 'text-red-600' : 'text-slate-700' }}">
                            {{ $log->temperature_celsius }}°
                        </td>
                        <td class="text-center text-xs text-slate-500">
                            {{ $log->min_safe_celsius }}° – {{ $log->max_safe_celsius }}°
                        </td>
                        <td class="text-center">
                            @if($log->is_within_range)
                                <span class="badge badge-green">✓ OK</span>
                            @else
                                <span class="badge badge-red">✗ Alert</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500 max-w-[160px] truncate">
                            {{ $log->corrective_action ?? '—' }}
                        </td>
                        <td class="text-xs text-slate-400">{{ $log->user->name }}</td>
                        <td>
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('food-safety.temperature.destroy', $log) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400">
                            No temperature logs for this date yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 7-day trend --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">7-Day Alert Summary</h3>
            <div class="space-y-2">
                @foreach(collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d')) as $day)
                @php
                    $dayData = $trend[$day] ?? null;
                    $total   = $dayData?->total ?? 0;
                    $alerts  = $dayData?->out_of_range ?? 0;
                    $label   = \Carbon\Carbon::parse($day)->format('D M d');
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-20 flex-shrink-0">{{ $label }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full {{ $alerts > 0 ? 'bg-red-400' : 'bg-emerald-400' }}"
                             style="width: {{ $total > 0 ? 100 : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold w-20 text-right {{ $alerts > 0 ? 'text-red-500' : 'text-slate-400' }}">
                        {{ $total }} logs{{ $alerts > 0 ? ", $alerts alert" . ($alerts > 1 ? 's' : '') : '' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function tempForm() {
    const ranges = @json(\App\Models\TemperatureLog::safeRanges());
    return {
        locationType: 'fridge',
        minSafe: ranges.fridge.min,
        maxSafe: ranges.fridge.max,
        temp: '',
        get isWithinRange() {
            if (this.temp === '') return true;
            const t = parseFloat(this.temp);
            return t >= parseFloat(this.minSafe) && t <= parseFloat(this.maxSafe);
        },
        setDefaults() {
            const r = ranges[this.locationType] || ranges.other;
            this.minSafe = r.min;
            this.maxSafe = r.max;
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