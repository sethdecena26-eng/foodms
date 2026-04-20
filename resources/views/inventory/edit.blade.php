@extends('layouts.app')
@section('title', 'Edit Ingredient')
@section('page-title', 'Edit: ' . $ingredient->name)
@section('page-subtitle', 'Update ingredient details (use Stock In/Adjust for quantity changes)')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl border border-slate-100 p-6">
        <form method="POST" action="{{ route('inventory.update', $ingredient) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="fms-label">Ingredient Name *</label>
                    <input type="text" name="name" value="{{ old('name', $ingredient->name) }}"
                           class="fms-input" required>
                    @error('name') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="fms-label">Unit *</label>
                    <input type="text" name="unit" value="{{ old('unit', $ingredient->unit) }}"
                           class="fms-input" required>
                    @error('unit') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="fms-label">Category</label>
                    <input type="text" name="category" value="{{ old('category', $ingredient->category) }}"
                           list="cat-list" class="fms-input">
                    <datalist id="cat-list">
                        <option value="Meat"><option value="Produce"><option value="Dairy">
                        <option value="Bread"><option value="Beverage"><option value="Condiment">
                        <option value="Pantry">
                    </datalist>
                </div>

                <div class="col-span-2">
                    <label class="fms-label">Current Stock</label>
                    <div class="fms-input bg-slate-50 text-slate-500 cursor-not-allowed">
                        {{ number_format($ingredient->quantity_in_stock, 3) }} {{ $ingredient->unit }}
                        <span class="text-xs">(use Stock In / Adjust to change)</span>
                    </div>
                </div>

                <div>
                    <label class="fms-label">Low Stock Threshold *</label>
                    <input type="number" name="low_stock_threshold"
                           value="{{ old('low_stock_threshold', $ingredient->low_stock_threshold) }}"
                           step="0.001" min="0" class="fms-input" required>
                    @error('low_stock_threshold') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="fms-label">Cost Per Unit (₱) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₱</span>
                        <input type="number" name="cost_per_unit"
                               value="{{ old('cost_per_unit', $ingredient->cost_per_unit) }}"
                               step="0.0001" min="0" class="fms-input pl-7" required>
                    </div>
                    <p class="text-xs text-amber-500 mt-1">⚠ Changing this will affect future order profit calculations.</p>
                    @error('cost_per_unit') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Save Changes</button>
                <a href="{{ route('inventory.index') }}" class="btn-ghost flex-1 text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

<style>
.fms-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:.35rem; }
.fms-input { width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; outline:none; transition:border-color .15s,box-shadow .15s; }
.fms-input:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgba(249,115,22,.1); }
.fms-error { font-size:.75rem; color:#ef4444; margin-top:.25rem; }
</style>