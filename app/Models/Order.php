<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'total_amount',
        'total_cost',
        'net_profit',
        'amount_tendered',
        'change_due',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'total_cost'      => 'decimal:4',
        'net_profit'      => 'decimal:4',
        'amount_tendered' => 'decimal:2',
        'change_due'      => 'decimal:2',
        'completed_at'    => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Static Factory ───────────────────────────────────────────────────────

    /**
     * Generate a human-readable, sequential order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd');
        $last   = static::where('order_number', 'like', $prefix . '%')
                        ->lockForUpdate()
                        ->max('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ─── Core Business Logic ─────────────────────────────────────────────────

    /**
     * Place and complete an order in a single atomic transaction.
     *
     * $cart = [
     *   ['menu_item_id' => 1, 'quantity' => 2],
     *   ['menu_item_id' => 3, 'quantity' => 1],
     * ]
     *
     * @throws InsufficientStockException
     * @throws \Throwable
     */
    public static function placeOrder(
        array  $cart,
        int    $cashierId,
        float  $amountTendered,
        string $paymentMethod = 'cash',
        float  $discountAmount = 0,
        string $notes = ''
    ): self {
        return DB::transaction(function () use (
            $cart, $cashierId, $amountTendered, $paymentMethod, $discountAmount, $notes
        ) {
            // ── 1. Load all menu items with their ingredients (eager, locked) ──
            $menuItemIds = array_column($cart, 'menu_item_id');

            $menuItems = MenuItem::with('ingredients')
                                 ->lockForUpdate()   // prevent race conditions
                                 ->findMany($menuItemIds)
                                 ->keyBy('id');

            // ── 2. Pre-flight stock validation (ALL items before ANY deduction) ──
            $errors = [];

            foreach ($cart as $line) {
                $item = $menuItems->get($line['menu_item_id']);

                if (! $item) {
                    $errors[] = "Menu item #{$line['menu_item_id']} not found.";
                    continue;
                }

                if (! $item->is_available) {
                    $errors[] = "{$item->name} is currently unavailable.";
                    continue;
                }

                $deficits = $item->checkStock((int) $line['quantity']);

                foreach ($deficits as $d) {
                    $errors[] = sprintf(
                        'Insufficient stock for [%s] in "%s". Need %.3f %s, have %.3f %s.',
                        $d['ingredient']->name,
                        $item->name,
                        $d['required'],
                        $d['ingredient']->unit,
                        $d['available'],
                        $d['ingredient']->unit
                    );
                }
            }

            if (! empty($errors)) {
                throw new InsufficientStockException(implode("\n", $errors));
            }

            // ── 3. Create the Order header ─────────────────────────────────────
            $order = static::create([
                'order_number'    => static::generateOrderNumber(),
                'user_id'         => $cashierId,
                'status'          => 'pending',
                'payment_method'  => $paymentMethod,
                'discount_amount' => $discountAmount,
                'notes'           => $notes,
                // Financials computed below
                'subtotal'        => 0,
                'total_amount'    => 0,
                'total_cost'      => 0,
                'net_profit'      => 0,
                'amount_tendered' => $amountTendered,
                'change_due'      => 0,
            ]);

            $subtotal  = 0;
            $totalCost = 0;

            // ── 4. Create order items + deduct stock ───────────────────────────
            foreach ($cart as $line) {
                $item = $menuItems->get($line['menu_item_id']);
                $qty  = (int) $line['quantity'];

                // Snapshot costs at the moment of sale
                $unitCost  = $item->currentCostSnapshot();
                $unitPrice = (float) $item->selling_price;
                $lineTotal = $unitPrice * $qty;
                $lineCost  = $unitCost  * $qty;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'unit_cost'    => $unitCost,
                    'line_total'   => $lineTotal,
                    'line_cost'    => $lineCost,
                    'line_profit'  => $lineTotal - $lineCost,
                ]);

                $subtotal  += $lineTotal;
                $totalCost += $lineCost;

                // ── 5. Auto-deduct each ingredient for this line item ──────────
                foreach ($item->ingredients as $ingredient) {
                    $deductQty = (float) $ingredient->pivot->quantity_required * $qty;

                    // Re-fetch with lock to get the freshest stock value
                    $freshIngredient = Ingredient::lockForUpdate()->find($ingredient->id);

                    $freshIngredient->deductStock(
                        qty:    $deductQty,
                        userId: $cashierId,
                        type:   'sale_deduction',
                        source: $order,
                        notes:  "Auto-deducted for Order #{$order->order_number} — {$item->name} x{$qty}"
                    );
                }
            }

            // ── 6. Finalise order totals ───────────────────────────────────────
            $totalAmount = max(0, $subtotal - $discountAmount);
            $netProfit   = $totalAmount - $totalCost;
            $changeDue   = max(0, $amountTendered - $totalAmount);

            $order->update([
                'subtotal'     => $subtotal,
                'total_amount' => $totalAmount,
                'total_cost'   => $totalCost,
                'net_profit'   => $netProfit,
                'change_due'   => $changeDue,
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            return $order->fresh('items');
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay(),
        ]);
    }
}