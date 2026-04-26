@extends('layouts.app')

@section('title', 'HACCP Checklist Items')
@section('page-title', 'HACCP Checklist Items')
@section('page-subtitle', 'Manage the master checklist template used every shift')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Add new item form --}}
    <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-display font-bold text-slate-800 mb-4">Add Checklist Item</h3>
            <form method="POST" action="{{ route('food-safety.haccp-items.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="fms-label">Title *</label>
                    <input type="text" name="title" class="fms-input" required
                           placeholder="e.g., Check fridge temps" value="{{ old('title') }}">
                    @error('title') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="fms-label">Description</label>
                    <textarea name="description" rows="2" class="fms-input resize-none"
                              placeholder="Optional detail or instruction">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="fms-label">Category *</label>
                    <select name="category" class="fms-input" required>
                        <option value="personal_hygiene">🧼 Personal Hygiene</option>
                        <option value="temperature_control">🌡️ Temperature Control</option>
                        <option value="cross_contamination">⚠️ Cross-Contamination</option>
                        <option value="cleaning">🫧 Cleaning & Sanitation</option>
                        <option value="storage">📦 Storage & Labelling</option>
                        <option value="other">📋 Other</option>
                    </select>
                </div>
                <div>
                    <label class="fms-label">Applies To *</label>
                    <select name="applies_to" class="fms-input" required>
                        <option value="opening">Opening shift only</option>
                        <option value="closing">Closing shift only</option>
                        <option value="both">Both shifts</option>
                    </select>
                </div>
                <div>
                    <label class="fms-label">Sort Order</label>
                    <input type="number" name="sort_order" class="fms-input" min="0"
                           value="{{ old('sort_order', $items->max('sort_order') + 1) }}">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">Add Item</button>
            </form>
        </div>
    </div>

    {{-- Item list --}}
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-display font-bold text-slate-800">All Checklist Items ({{ $items->count() }})</h3>
                <a href="{{ route('food-safety.haccp') }}" class="btn-ghost text-xs">View Checklist →</a>
            </div>
            <table class="fms-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Title</th>
                        <th class="text-center">Applies To</th>
                        <th class="text-left">Category</th>
                        <th class="text-center">Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="{{ !$item->is_active ? 'opacity-40' : '' }}">
                        <td class="text-slate-400 text-xs">{{ $item->sort_order }}</td>
                        <td>
                            <p class="font-medium text-slate-700 text-sm">{{ $item->title }}</p>
                            @if($item->description)
                                <p class="text-xs text-slate-400 mt-0.5">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $item->applies_to === 'opening' ? 'badge-blue' : ($item->applies_to === 'closing' ? 'badge-slate' : 'badge-amber') }}">
                                {{ ucfirst($item->applies_to) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs text-slate-500">
                                {{ str_replace('_', ' ', ucfirst($item->category)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="badge badge-green">Yes</span>
                            @else
                                <span class="badge badge-slate">No</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('food-safety.haccp-items.destroy', $item) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs {{ $item->is_active ? 'text-amber-500 hover:text-amber-700' : 'text-emerald-500 hover:text-emerald-700' }}">
                                    {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">No checklist items yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.fms-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:.35rem; }
.fms-input { width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; outline:none; transition:border-color .15s,box-shadow .15s; }
.fms-input:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgba(249,115,22,.1); }
.fms-error { font-size:.75rem; color:#ef4444; margin-top:.25rem; }
</style>
@endsection