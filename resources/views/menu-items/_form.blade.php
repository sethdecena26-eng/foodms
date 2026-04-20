{{--
    Shared form partial used by both create.blade.php and edit.blade.php
    Variables expected:
      $item        (MenuItem|null)  — null on create
      $ingredients (Collection)     — all Ingredient models
      $categories  (Collection)     — distinct category strings
      $formAction  (string)         — route URL
      $formMethod  (string)         — PUT on edit, POST on create
--}}
<div x-data="recipeBuilder()" x-init="init()" class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ═══════════════════════════════ LEFT: Item Details ═══ --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Basic info card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
            <h3 class="font-display font-bold text-slate-800 mb-5">Item Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Name --}}
                <div class="sm:col-span-2">
                    <label class="fms-label">Item Name *</label>
                    <input type="text" name="name"
                           value="{{ old('name', $item->name ?? '') }}"
                           placeholder="e.g., Classic Cheeseburger"
                           class="fms-input" required>
                    @error('name') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="fms-label">Category *</label>
                    <input type="text" name="category"
                           value="{{ old('category', $item->category ?? '') }}"
                           placeholder="e.g., Burgers, Drinks, Sides"
                           list="category-suggestions"
                           class="fms-input" required>
                    <datalist id="category-suggestions">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    @error('category') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                {{-- Selling Price --}}
                <div>
                    <label class="fms-label">Price</label>
                    <div class="relative">
                        <span class="absolute left-1 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₱</span>
                        <input type="number" name="selling_price" step="0.01" min="0"
                               x-model="sellingPrice"
                               value="{{ old('selling_price', $item->selling_price ?? '') }}"
                               class="fms-input pl-7" required>
                    </div>
                    @error('selling_price') <p class="fms-error">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label class="fms-label">Description</label>
                    <textarea name="description" rows="2"
                              placeholder="Short description shown on POS…"
                              class="fms-input resize-none">{{ old('description', $item->description ?? '') }}</textarea>
                </div>

                {{-- Image --}}
                <div>
                    <label class="fms-label">Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4
                                  file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100
                                  cursor-pointer">
                    @if(isset($item) && $item->image)
                        <div class="mt-2 flex items-center gap-2">
                            <img src="{{ asset('storage/' . $item->image) }}" class="w-12 h-12 rounded-lg object-cover">
                            <p class="text-xs text-slate-400">Current image</p>
                        </div>
                    @endif
                </div>

                {{-- Available toggle --}}
                <div class="flex items-center gap-3 self-end pb-1">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1"
                               class="sr-only peer"
                               {{ old('is_available', $item->is_available ?? true) ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-slate-200 peer-focus:ring-2 peer-focus:ring-orange-300 rounded-full peer
                                    peer-checked:after:translate-x-5 peer-checked:after:border-white
                                    after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                    after:bg-white after:border-slate-300 after:border after:rounded-full
                                    after:h-4 after:w-4 after:transition-all
                                    peer-checked:bg-orange-500"></div>
                        <span class="ml-2 text-sm font-medium text-slate-700">Available on POS</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ═══ Recipe Builder ═══ --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-display font-bold text-slate-800">Recipe Builder</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Define which ingredients and quantities make up one serving</p>
                </div>
                <button type="button" @click="addRow()"
                        class="btn-primary text-xs py-1.5 px-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Ingredient
                </button>
            </div>

            {{-- Recipe rows --}}
            <div class="space-y-2" id="recipe-rows">
                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl group">

                        {{-- Ingredient select --}}
                        <div class="flex-1 min-w-0">
                            <select :name="'recipe[' + idx + '][ingredient_id]'"
                                    x-model="row.ingredient_id"
                                    @change="updateRowCost(idx)"
                                    class="fms-input py-2 text-sm">
                                <option value="">— Select ingredient —</option>
                                @foreach($ingredients->groupBy('category') as $cat => $group)
                                    <optgroup label="{{ $cat ?? 'Other' }}">
                                        @foreach($group as $ing)
                                        <option value="{{ $ing->id }}"
                                                data-cost="{{ $ing->cost_per_unit }}"
                                                data-unit="{{ $ing->unit }}">
                                            {{ $ing->name }} ({{ $ing->unit }})
                                        </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Quantity --}}
                        <div class="w-28 flex-shrink-0">
                            <div class="relative">
                                <input type="number"
                                       :name="'recipe[' + idx + '][quantity_required]'"
                                       x-model="row.quantity"
                                       @input="updateRowCost(idx)"
                                       step="0.001" min="0.001"
                                       placeholder="Qty"
                                       class="fms-input py-2 text-sm pr-8">
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400"
                                      x-text="row.unit || ''"></span>
                            </div>
                        </div>

                        {{-- Row cost display --}}
                        <div class="w-20 text-right flex-shrink-0">
                            <p class="text-xs text-slate-400">Cost</p>
                            <p class="text-sm font-semibold text-slate-700">
                                ₱<span x-text="row.lineCost.toFixed(4)"></span>
                            </p>
                        </div>

                        {{-- Remove --}}
                        <button type="button" @click="removeRow(idx)"
                                class="w-7 h-7 rounded-lg flex items-center justify-center
                                       text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <div x-show="rows.length === 0"
                     class="text-center py-8 text-slate-300 border-2 border-dashed border-slate-200 rounded-xl">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <p class="text-sm">No ingredients added yet</p>
                    <p class="text-xs mt-1">Click "Add Ingredient" to build the recipe</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════ RIGHT: Costing Panel ═══ --}}
    <div class="space-y-5">

        {{-- Live costing card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 sticky top-6">
            <h3 class="font-display font-bold text-slate-800 mb-4">Costing Analysis</h3>

            {{-- Total cost --}}
            <div class="bg-slate-50 rounded-xl p-4 mb-4">
                <p class="text-xs text-slate-400 uppercase tracking-wide font-semibold mb-1">Total Ingredient Cost</p>
                <p class="text-2xl font-display font-bold text-slate-800">
                    ₱<span x-text="totalCost.toFixed(4)">0.0000</span>
                </p>
            </div>

            {{-- Selling price echo --}}
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Selling Price</span>
                    <span class="font-semibold text-slate-800">
                        ₱<span x-text="parseFloat(sellingPrice || 0).toFixed(2)">0.00</span>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Gross Profit</span>
                    <span class="font-semibold text-emerald-600">
                        ₱<span x-text="grossProfit.toFixed(2)">0.00</span>
                    </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                    <span class="text-slate-500">Profit Margin</span>
                    <span class="text-sm font-bold px-2.5 py-1 rounded-full"
                          :class="marginClass">
                        <span x-text="marginPct.toFixed(1)">0.0</span>%
                    </span>
                </div>
            </div>

            {{-- Suggested price --}}
            <div class="mt-4 p-3 rounded-xl border" :class="suggestedBorderClass">
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" :class="suggestedLabelClass">
                    Suggested Price (30% food cost)
                </p>
                <p class="text-lg font-display font-bold text-slate-700">
                    ₱<span x-text="suggestedPrice.toFixed(2)">0.00</span>
                </p>
                <p class="text-xs mt-1" :class="suggestedLabelClass" x-text="suggestedHint"></p>
            </div>

            {{-- Margin health bar --}}
            <div class="mt-4">
                <div class="flex justify-between text-xs text-slate-400 mb-1">
                    <span>Margin health</span>
                    <span x-text="marginPct.toFixed(1) + '%'"></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300"
                         :style="'width:' + Math.min(100, marginPct) + '%;'"
                         :class="marginBarClass">
                    </div>
                </div>
                <div class="flex justify-between text-xs text-slate-300 mt-1">
                    <span>0%</span><span>30%</span><span>50%</span><span>100%</span>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-primary w-full justify-center mt-5 py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isset($item) ? 'Update Menu Item' : 'Create Menu Item' }}
            </button>

            @if(isset($item))
            <a href="{{ route('menu-items.index') }}"
               class="btn-ghost w-full text-center mt-2 block">Cancel</a>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ingredient data map: id → { cost_per_unit, unit }
const INGREDIENT_DATA = {
    @foreach($ingredients as $ing)
    {{ $ing->id }}: { cost: {{ (float) $ing->cost_per_unit }}, unit: "{{ $ing->unit }}" },
    @endforeach
};

// Existing recipe rows (populated on edit)
const EXISTING_RECIPE = [
    @isset($item)
        @foreach($item->ingredients as $ing)
        { ingredient_id: "{{ $ing->id }}", quantity: "{{ $ing->pivot->quantity_required }}", unit: "{{ $ing->unit }}", lineCost: {{ (float) $ing->cost_per_unit * (float) $ing->pivot->quantity_required }} },
        @endforeach
    @endisset
];

function recipeBuilder() {
    return {
        rows: [],
        sellingPrice: {{ old('selling_price', isset($item) ? $item->selling_price : 0) }},

        init() {
            this.rows = EXISTING_RECIPE.length
                ? EXISTING_RECIPE.map(r => ({ ...r }))
                : [];
        },

        addRow() {
            this.rows.push({ ingredient_id: '', quantity: '', unit: '', lineCost: 0 });
        },

        removeRow(idx) {
            this.rows.splice(idx, 1);
        },

        updateRowCost(idx) {
            const row  = this.rows[idx];
            const data = INGREDIENT_DATA[row.ingredient_id];
            if (data) {
                row.unit     = data.unit;
                row.lineCost = (parseFloat(row.quantity) || 0) * data.cost;
            } else {
                row.lineCost = 0;
                row.unit     = '';
            }
        },

        get totalCost() {
            return this.rows.reduce((s, r) => s + (r.lineCost || 0), 0);
        },

        get grossProfit() {
            return (parseFloat(this.sellingPrice) || 0) - this.totalCost;
        },

        get marginPct() {
            const sp = parseFloat(this.sellingPrice) || 0;
            return sp > 0 ? (this.grossProfit / sp) * 100 : 0;
        },

        get suggestedPrice() {
            return this.totalCost > 0 ? this.totalCost / 0.30 : 0;
        },

        get marginClass() {
            const m = this.marginPct;
            if (m >= 30) return 'bg-green-100 text-green-600';
            if (m >= 15) return 'bg-amber-100 text-amber-600';
            return 'bg-red-100 text-red-600';
        },

        get marginBarClass() {
            const m = this.marginPct;
            if (m >= 30) return 'bg-emerald-500';
            if (m >= 15) return 'bg-amber-400';
            return 'bg-red-400';
        },

        get suggestedBorderClass() {
            const sp = parseFloat(this.sellingPrice) || 0;
            if (sp >= this.suggestedPrice && this.suggestedPrice > 0) return 'border-green-200 bg-green-50';
            return 'border-amber-200 bg-amber-50';
        },

        get suggestedLabelClass() {
            const sp = parseFloat(this.sellingPrice) || 0;
            if (sp >= this.suggestedPrice && this.suggestedPrice > 0) return 'text-green-600';
            return 'text-amber-600';
        },

        get suggestedHint() {
            const sp  = parseFloat(this.sellingPrice) || 0;
            const sug = this.suggestedPrice;
            if (sug <= 0) return 'Add ingredients to calculate';
            if (sp >= sug) return '✓ Price meets the 30% rule';
            return `↑ Price ₱${(sug - sp).toFixed(2)} below suggestion`;
        },
    };
}
</script>
@endpush

<style>
.fms-label  { display: block; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-bottom: .35rem; }
.fms-input  { width: 100%; padding: .55rem .75rem; border: 1px solid #e2e8f0; border-radius: .5rem; font-size: .875rem; color: #1e293b; background: #fff; transition: border-color .15s, box-shadow .15s; outline: none; }
.fms-input:focus { border-color: #fb923c; box-shadow: 0 0 0 3px rgba(249,115,22,.1); }
.fms-error  { font-size: .75rem; color: #ef4444; margin-top: .25rem; }
</style>