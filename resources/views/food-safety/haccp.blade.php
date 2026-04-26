@extends('layouts.app')

@section('title', 'HACCP Checklist')
@section('page-title', 'HACCP Checklist')
@section('page-subtitle', 'Digital shift opening & closing food safety compliance')

@section('content')

{{-- Date picker --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm font-medium text-slate-600">Date:</label>
        <input type="date" name="date" value="{{ $date }}"
               onchange="this.form.submit()"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
    </form>
    <div class="flex gap-2">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('food-safety.haccp-items') }}" class="btn-ghost text-xs">
            Manage Checklist Items
        </a>
        @endif
    </div>
</div>

{{-- Two columns: Opening and Closing --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">

    @foreach(['opening' => $opening, 'closing' => $closing] as $shiftType => $checklist)
    @php
        $templateItems = $shiftType === 'opening' ? $openingItems : $closingItems;
        $isCompleted   = $checklist && $checklist->status === 'completed';
        $passCount     = $checklist ? $checklist->items->where('status','pass')->count() : 0;
        $failCount     = $checklist ? $checklist->items->where('status','fail')->count() : 0;
        $totalItems    = $templateItems->count();
        $shiftIcon     = $shiftType === 'opening' ? '🌅' : '🌙';
    @endphp

    <div class="bg-white rounded-2xl border {{ $isCompleted ? 'border-emerald-200' : 'border-slate-100' }} overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 border-b {{ $isCompleted ? 'border-emerald-100 bg-emerald-50' : 'border-slate-100' }} flex items-center justify-between">
            <div>
                <h3 class="font-display font-bold text-slate-800">
                    {{ $shiftIcon }} {{ ucfirst($shiftType) }} Checklist
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $totalItems }} items
                    @if($checklist)
                        · {{ $passCount }} passed · {{ $failCount }} failed
                        · by {{ $checklist->user->name }}
                    @endif
                </p>
            </div>
            @if($isCompleted)
                <span class="badge badge-green">✓ Completed</span>
            @else
                <span class="badge badge-amber">Pending</span>
            @endif
        </div>

        {{-- Checklist form --}}
        <form method="POST" action="{{ route('food-safety.haccp.store') }}" class="p-5">
            @csrf
            <input type="hidden" name="shift_type"     value="{{ $shiftType }}">
            <input type="hidden" name="checklist_date" value="{{ $date }}">

            {{-- Group items by category --}}
            @php
                $grouped = $templateItems->groupBy('category');
                $catLabels = [
                    'personal_hygiene'    => '🧼 Personal Hygiene',
                    'temperature_control' => '🌡️ Temperature Control',
                    'cross_contamination' => '⚠️ Cross-Contamination',
                    'cleaning'            => '🫧 Cleaning & Sanitation',
                    'storage'             => '📦 Storage & Labelling',
                    'other'               => '📋 Other',
                ];
            @endphp

            <div class="space-y-5">
                @foreach($grouped as $category => $items)
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                        {{ $catLabels[$category] ?? $category }}
                    </p>
                    <div class="space-y-2">
                        @foreach($items as $idx => $item)
                        @php
                            // Find existing response if checklist already submitted
                            $response = $checklist
                                ? $checklist->items->firstWhere('haccp_item_id', $item->id)
                                : null;
                            $currentStatus = $response?->status ?? 'na';
                        @endphp
                        <div class="border border-slate-100 rounded-xl p-3 {{ $currentStatus === 'fail' ? 'border-red-200 bg-red-50' : ($currentStatus === 'pass' ? 'bg-emerald-50 border-emerald-100' : '') }}">
                            <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">

                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-700">{{ $item->title }}</p>
                                    @if($item->description)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $item->description }}</p>
                                    @endif
                                </div>
                                {{-- Pass / Fail / N/A toggle --}}
                                <div class="flex gap-1 flex-shrink-0">
                                    @foreach(['pass' => '✓', 'fail' => '✗', 'na' => 'N/A'] as $val => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="items[{{ $idx }}][status]"
                                               value="{{ $val }}"
                                               class="sr-only peer"
                                               {{ $currentStatus === $val ? 'checked' : '' }}>
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold border transition-all
                                            peer-checked:{{ $val === 'pass' ? 'bg-emerald-500 text-white border-emerald-500' : ($val === 'fail' ? 'bg-red-500 text-white border-red-500' : 'bg-slate-600 text-white border-slate-600') }}
                                            {{ $currentStatus !== $val ? 'border-slate-200 text-slate-400 hover:border-slate-300' : '' }}">
                                            {{ $label }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Notes field --}}
                            <input type="text"
                                   name="items[{{ $idx }}][notes]"
                                   value="{{ $response?->notes }}"
                                   placeholder="Notes (optional)"
                                   class="mt-2 w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg
                                          focus:ring-1 focus:ring-orange-300 focus:border-orange-400 outline-none bg-white">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Supervisor notes --}}
            <div class="mt-4">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">
                    Supervisor Notes
                </label>
                <textarea name="supervisor_notes" rows="2"
                          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg
                                 focus:ring-1 focus:ring-orange-300 focus:border-orange-400 outline-none resize-none">{{ $checklist?->supervisor_notes }}</textarea>
            </div>

            <button type="submit" class="btn-primary w-full justify-center mt-4">
                {{ $isCompleted ? 'Update ' : 'Submit ' }} {{ ucfirst($shiftType) }} Checklist
            </button>
        </form>
    </div>
    @endforeach
</div>

{{-- Recent history --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-display font-bold text-slate-800">7-Day Compliance History</h3>
    </div>
    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">Date</th>
                <th class="text-center">Opening</th>
                <th class="text-center">Closing</th>
                <th class="text-center">Overall</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $histDate => $checklists)
            @php
                $open  = $checklists->firstWhere('shift_type', 'opening');
                $close = $checklists->firstWhere('shift_type', 'closing');
                $bothDone = $open && $close;
            @endphp
            <tr>
                <td class="font-medium text-slate-700">
                    {{ \Carbon\Carbon::parse($histDate)->format('D, M d') }}
                </td>
                <td class="text-center">
                    @if($open)
                        <span class="badge badge-green">✓ Done</span>
                    @else
                        <span class="badge badge-slate">Missed</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($close)
                        <span class="badge badge-green">✓ Done</span>
                    @else
                        <span class="badge badge-slate">Missed</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($bothDone)
                        <span class="badge badge-green">100%</span>
                    @elseif($open || $close)
                        <span class="badge badge-amber">50%</span>
                    @else
                        <span class="badge badge-red">0%</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-8 text-slate-400">No checklist history yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection