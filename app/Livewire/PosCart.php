<?php

namespace App\Livewire;

use App\Models\MenuItem;
use App\Models\Order;
use App\Exceptions\InsufficientStockException;
use Livewire\Component;
use Livewire\Attributes\Computed;

class PosCart extends Component
{
    // ── No type declarations on public properties ─────────────────────────────
    // Livewire 3 hydrates these from browser state as strings.
    // PHP typed float/int properties reject string input and throw
    // PropertyNotFoundException during Livewire's state sync.
    public $search         = '';
    public $cart           = [];
    public $amountTendered = 0;
    public $paymentMethod  = 'cash';
    public $discountAmount = 0;
    public $notes          = '';
    public $showReceipt    = false;
    public $lastOrder      = null;
    public $errorMessage   = '';

    // ── Computed properties ───────────────────────────────────────────────────
    // No return type declarations — Livewire 3 #[Computed] does not support them

    #[Computed]
    public function menuItems()
    {
        return MenuItem::with('ingredients')
            ->where('is_available', true)
            ->when($this->search, fn($q) =>
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('category', 'like', "%{$this->search}%");
                })
            )
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function cartItems()
    {
        if (empty($this->cart)) {
            return collect();
        }

        return MenuItem::with('ingredients')
            ->whereIn('id', array_keys($this->cart))
            ->get()
            ->map(fn(MenuItem $item) => [
                'id'         => $item->id,
                'name'       => $item->name,
                'price'      => (float) $item->selling_price,
                'qty'        => $this->cart[$item->id],
                'line_total' => (float) $item->selling_price * $this->cart[$item->id],
            ]);
    }

    #[Computed]
    public function subtotal()
    {
        return $this->cartItems->sum('line_total');
    }

    #[Computed]
    public function total()
    {
        return max(0, $this->subtotal - (float) $this->discountAmount);
    }

    #[Computed]
    public function changeDue()
    {
        return max(0, (float) $this->amountTendered - $this->total);
    }

    // ── Cart actions ──────────────────────────────────────────────────────────

    public function addItem(int $id): void
    {
        $this->errorMessage  = '';
        $this->cart[$id]     = ($this->cart[$id] ?? 0) + 1;
    }

    public function removeItem(int $id): void
    {
        unset($this->cart[$id]);
    }

    public function decrementItem(int $id): void
    {
        if (($this->cart[$id] ?? 0) <= 1) {
            $this->removeItem($id);
            return;
        }
        $this->cart[$id]--;
    }

    public function clearCart(): void
    {
        $this->reset(['cart', 'amountTendered', 'discountAmount', 'notes', 'errorMessage']);
    }

    public function setTendered($amount): void
    {
        $this->amountTendered = (float) $amount;
    }

    // ── Place order ───────────────────────────────────────────────────────────

    public function placeOrder(): void
    {
        $this->errorMessage = '';

        $this->validate([
            'cart'           => ['required', 'array', 'min:1'],
            'amountTendered' => ['required', 'numeric', 'min:0'],
            'paymentMethod'  => ['required', 'in:cash,card,gcash,other'],
        ]);

        if ($this->paymentMethod === 'cash' && (float) $this->amountTendered < $this->total) {
            $this->errorMessage = 'Amount tendered is less than the order total.';
            return;
        }

        $cartPayload = collect($this->cart)
            ->map(fn($qty, $id) => [
                'menu_item_id' => (int) $id,
                'quantity'     => (int) $qty,
            ])
            ->values()
            ->all();

        try {
            $order = Order::placeOrder(
                cart:           $cartPayload,
                cashierId:      auth()->id(),
                amountTendered: (float) $this->amountTendered,
                paymentMethod:  $this->paymentMethod,
                discountAmount: (float) $this->discountAmount,
                notes:          $this->notes
            );

            $order->load('items.menuItem');

            $this->lastOrder = [
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'change_due'   => $order->change_due,
                'net_profit'   => $order->net_profit,
                'items'        => $order->items->map(fn($i) => [
                    'name'       => $i->menuItem->name,
                    'qty'        => $i->quantity,
                    'line_total' => $i->line_total,
                ])->all(),
            ];

            $this->clearCart();
            $this->showReceipt = true;

        } catch (InsufficientStockException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Unexpected error. Please try again.';
            logger()->error('POS Livewire error', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.pos-cart');
    }
}