<div class="flex h-full gap-0 -m-6 overflow-hidden" style="height: calc(100vh - 64px)">

    {{-- ═══════════════════════════════ MENU GRID (left) ═══ --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Search bar --}}
        <div class="px-5 pt-5 pb-3 bg-white border-b border-slate-100 flex-shrink-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.200ms="search"
                       type="search"
                       placeholder="Search menu items…"
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg
                              text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400
                              focus:bg-white transition-colors outline-none">
            </div>
        </div>

        {{-- Grid --}}
        <div class="flex-1 overflow-y-auto p-5">
            @php $categories = $this->menuItems->groupBy('category'); @endphp

            @forelse($categories as $category => $items)
            <div class="mb-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ $category }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 2xl:grid-cols-4 gap-3">
                    @foreach($items as $item)
                    @php
                        $inCart   = isset($this->cart[$item->id]);
                        $cartQty  = $this->cart[$item->id] ?? 0;
                        $lowStock = $item->ingredients->contains(fn($i) => $i->quantity_in_stock <= $i->low_stock_threshold);
                    @endphp
                    <div wire:click="addItem({{ $item->id }})"
                         class="menu-card p-3 select-none {{ $inCart ? 'border-orange-400' : '' }}">

                        {{-- Image / placeholder --}}
                        <div class="w-full aspect-square rounded-lg mb-2 overflow-hidden bg-slate-100 flex items-center justify-center">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 3C7 3 3 7.58 3 11c0 1.93.78 3.68 2.05 4.97L6 20h12l.95-4.03C20.22 14.68 21 12.93 21 11c0-3.42-4-8-9-8z"/>
                                </svg>
                            @endif
                        </div>

                        <p class="text-sm font-semibold text-slate-700 leading-tight truncate">{{ $item->name }}</p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-sm font-bold" style="color:var(--accent)">
                                ₱{{ number_format($item->selling_price, 2) }}
                            </span>
                            @if($lowStock)
                                <span class="text-xs text-amber-500 font-semibold">Low</span>
                            @endif
                        </div>

                        {{-- Cart qty badge --}}
                        @if($inCart)
                        <div class="mt-2 flex items-center justify-between bg-orange-50 rounded-lg px-2 py-1">
                            <button wire:click.stop="decrementItem({{ $item->id }})"
                                    class="w-5 h-5 rounded-full bg-orange-200 text-orange-700 text-xs font-bold flex items-center justify-center hover:bg-orange-300 transition-colors">
                                −
                            </button>
                            <span class="text-xs font-bold text-orange-600">{{ $cartQty }}</span>
                            <button wire:click.stop="addItem({{ $item->id }})"
                                    class="w-5 h-5 rounded-full bg-orange-200 text-orange-700 text-xs font-bold flex items-center justify-center hover:bg-orange-300 transition-colors">
                                +
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-48 text-slate-400">
                <svg class="w-10 h-10 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-sm">No menu items found</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════ CART (right) ═══ --}}
    <div id="pos-cart" class="w-80 flex-shrink-0 flex flex-col overflow-hidden">

        {{-- Cart header --}}
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <h2 class="font-display font-bold text-slate-800">
                Current Order
                @if(!empty($this->cart))
                    <span class="ml-1.5 text-xs font-semibold text-white px-1.5 py-0.5 rounded-full"
                          style="background:var(--accent)">
                        {{ array_sum($this->cart) }}
                    </span>
                @endif
            </h2>
            @if(!empty($this->cart))
            <button wire:click="clearCart"
                    class="text-xs text-slate-400 hover:text-red-500 transition-colors font-medium">
                Clear all
            </button>
            @endif
        </div>

        {{-- Cart items --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            @forelse($this->cartItems as $line)
            <div class="flex items-center gap-3 py-2 border-b border-slate-50">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $line['name'] }}</p>
                    <p class="text-xs text-slate-400">₱{{ number_format($line['price'], 2) }} each</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button wire:click="decrementItem({{ $line['id'] }})"
                            class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-sm font-bold
                                   flex items-center justify-center hover:bg-slate-200 transition-colors">
                        −
                    </button>
                    <span class="w-5 text-center text-sm font-semibold text-slate-700">{{ $line['qty'] }}</span>
                    <button wire:click="addItem({{ $line['id'] }})"
                            class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-sm font-bold
                                   flex items-center justify-center hover:bg-slate-200 transition-colors">
                        +
                    </button>
                </div>
                <span class="text-sm font-bold text-slate-700 w-16 text-right">
                    ₱{{ number_format($line['line_total'], 2) }}
                </span>
                <button wire:click="removeItem({{ $line['id'] }})"
                        class="text-slate-300 hover:text-red-400 transition-colors ml-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-32 text-slate-300">
                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-xs">Cart is empty</p>
            </div>
            @endforelse
        </div>

        {{-- Totals + Payment --}}
        @if(!empty($this->cart))
        <div class="border-t border-slate-100 px-4 py-4 space-y-3 flex-shrink-0 bg-slate-50">

            {{-- Discount --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-slate-500 w-20 flex-shrink-0">Discount</label>
                <div class="relative flex-1">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">₱</span>
                    <input wire:model.live="discountAmount"
                           type="number" min="0" step="0.01"
                           class="w-full pl-6 pr-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-1 focus:ring-orange-300 focus:border-orange-400 outline-none bg-white">
                </div>
            </div>

            {{-- Totals --}}
            <div class="space-y-1">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Subtotal</span>
                    <span>₱{{ number_format($this->subtotal, 2) }}</span>
                </div>
                @if($this->discountAmount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Discount</span>
                    <span>−₱{{ number_format($this->discountAmount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-bold text-slate-800 pt-1 border-t border-slate-200">
                    <span>Total</span>
                    <span style="color:var(--accent)">₱{{ number_format($this->total, 2) }}</span>
                </div>
            </div>

            {{-- Payment method --}}
            <div class="grid grid-cols-4 gap-1">
                @foreach(['cash' => '💵', 'card' => '💳', 'gcash' => '📱', 'other' => '…'] as $method => $icon)
                <button wire:click="$set('paymentMethod', '{{ $method }}')"
                        class="py-1.5 text-xs font-semibold rounded-lg border transition-all
                               {{ $this->paymentMethod === $method
                                  ? 'border-orange-400 bg-orange-50 text-orange-600'
                                  : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300' }}">
                    {{ $icon }} {{ ucfirst($method) }}
                </button>
                @endforeach
            </div>

            {{-- Tendered --}}
            @if($this->paymentMethod === 'cash')
            <div>
                <label class="text-xs text-slate-500 mb-1 block">Amount Tendered</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">₱</span>
                    <input wire:model.live="amountTendered"
                           type="number" min="0" step="0.01"
                           class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm font-semibold
                                  focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                </div>
                {{-- Quick cash buttons --}}
                <div class="grid grid-cols-4 gap-1 mt-1.5">
                    @foreach([20, 50, 100, 500] as $bill)
                    <button wire:click="setTendered({{ $bill }})"
                            class="text-xs py-1 rounded-lg border border-slate-200 bg-white text-slate-500
                                   hover:bg-slate-50 hover:border-slate-300 transition-colors font-medium">
                        {{ $bill }}
                    </button>
                    @endforeach
                </div>
                @if($this->amountTendered >= $this->total && $this->total > 0)
                <p class="text-xs font-bold text-emerald-600 mt-1.5 text-right">
                    Change: ₱{{ number_format($this->changeDue, 2) }}
                </p>
                @endif
            </div>
            @endif

            {{-- Notes --}}
            <input wire:model="notes"
                   type="text"
                   placeholder="Order notes (optional)…"
                   class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1
                          focus:ring-orange-300 focus:border-orange-400 outline-none">

            {{-- Error --}}
            @if($this->errorMessage)
            <div class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                {{ $this->errorMessage }}
            </div>
            @endif

            {{-- Place order button --}}
            <button wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    class="btn-primary w-full justify-center py-3">
                <span wire:loading.remove>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Place Order
                </span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Processing…
                </span>
            </button>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════ RECEIPT MODAL ═══ --}}
@if($this->showReceipt && $this->lastOrder)
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-6 pt-6 pb-4 text-center border-b border-slate-100">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
                 style="background:#f0fdf4">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="font-display font-bold text-slate-800 text-lg">Order Complete!</h3>
            <p class="text-sm text-slate-500 mt-0.5">{{ $this->lastOrder['order_number'] }}</p>
        </div>

        {{-- Items --}}
        <div class="px-6 py-4 space-y-1.5 max-h-40 overflow-y-auto">
            @foreach($this->lastOrder['items'] as $line)
            <div class="flex justify-between text-sm">
                <span class="text-slate-600">{{ $line['name'] }} × {{ $line['qty'] }}</span>
                <span class="font-semibold text-slate-700">₱{{ number_format($line['line_total'], 2) }}</span>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="px-6 pb-5 space-y-2 border-t border-slate-100 pt-4">
            <div class="flex justify-between font-bold text-slate-800">
                <span>Total</span>
                <span style="color:var(--accent)">₱{{ number_format($this->lastOrder['total_amount'], 2) }}</span>
            </div>
            @if($this->lastOrder['change_due'] > 0)
            <div class="flex justify-between text-emerald-600 font-bold">
                <span>Change</span>
                <span>₱{{ number_format($this->lastOrder['change_due'], 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-xs text-slate-400 pt-1 border-t border-slate-50">
                <span>Net Profit</span>
                <span class="text-emerald-500 font-semibold">₱{{ number_format($this->lastOrder['net_profit'], 2) }}</span>
            </div>

            <button wire:click="$set('showReceipt', false)"
                    class="btn-primary w-full justify-center mt-2">
                New Order
            </button>
        </div>
    </div>
</div>
@endif