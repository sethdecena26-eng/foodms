{{-- ============================================================
     resources/views/inventory/create.blade.php
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Add Ingredient')
@section('page-title', 'Add Ingredient')
@section('page-subtitle', 'Register a new raw ingredient into inventory')
 
@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl border border-slate-100 p-6">
        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
            @csrf
 
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="fms-label">Ingredient Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="fms-input" required placeholder="e.g., Beef Patty">
                    @error('name') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
 
                <div>
                    <label class="fms-label">Unit *</label>
                    <input type="text" name="unit" value="{{ old('unit') }}"
                           list="unit-list"
                           class="fms-input" required placeholder="grams, pcs, ml…">
                    <datalist id="unit-list">
                        <option value="grams"><option value="kg"><option value="ml">
                        <option value="liters"><option value="pcs"><option value="cups">
                    </datalist>
                    @error('unit') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
 
                <div>
                    <label class="fms-label">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           list="cat-list" class="fms-input" placeholder="Meat, Produce…">
                    <datalist id="cat-list">
                        <option value="Meat"><option value="Produce"><option value="Dairy">
                        <option value="Bread"><option value="Beverage"><option value="Condiment">
                        <option value="Pantry">
                    </datalist>
                </div>
 
                <div>
                    <label class="fms-label">Initial Stock *</label>
                    <input type="number" name="quantity_in_stock" value="{{ old('quantity_in_stock', 0) }}"
                           step="0.001" min="0" class="fms-input" required>
                    @error('quantity_in_stock') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
 
                <div>
                    <label class="fms-label">Low Stock Threshold *</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 0) }}"
                           step="0.001" min="0" class="fms-input" required>
                    <p class="text-xs text-slate-400 mt-1">Alert triggers when stock reaches this level</p>
                    @error('low_stock_threshold') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
 
                <div class="col-span-2">
                    <label class="fms-label">Cost Per Unit (₱) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₱</span>
                        <input type="number" name="cost_per_unit" value="{{ old('cost_per_unit', 0) }}"
                               step="0.0001" min="0" class="fms-input pl-7" required>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Price per 1 unit (e.g., ₱0.12 per gram)</p>
                    @error('cost_per_unit') <p class="fms-error">{{ $message }}</p> @enderror
                </div>
            </div>
 
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Add Ingredient</button>
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