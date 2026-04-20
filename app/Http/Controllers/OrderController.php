<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->withCount('items')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->paginate(25)->withQueryString();

        $todayStats     = Order::completed()->today()
                               ->selectRaw('COUNT(*) as cnt, SUM(total_amount) as rev, SUM(net_profit) as profit')
                               ->first();
        $cancelledCount = Order::where('status', 'cancelled')->whereDate('created_at', today())->count();

        return view('orders.index', [
            'orders'         => $orders,
            'todayCount'     => $todayStats->cnt    ?? 0,
            'todayRevenue'   => $todayStats->rev    ?? 0,
            'todayProfit'    => $todayStats->profit ?? 0,
            'cancelledCount' => $cancelledCount,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cart'                => ['required', 'array', 'min:1'],
            'cart.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'cart.*.quantity'     => ['required', 'integer', 'min:1', 'max:99'],
            'amount_tendered'     => ['required', 'numeric', 'min:0'],
            'payment_method'      => ['required', 'in:cash,card,gcash,other'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = Order::placeOrder(
                cart:           $data['cart'],
                cashierId:      $request->user()->id,
                amountTendered: (float) $data['amount_tendered'],
                paymentMethod:  $data['payment_method'],
                discountAmount: (float) ($data['discount_amount'] ?? 0),
                notes:          $data['notes'] ?? ''
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => true,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'change_due'   => $order->change_due,
                    'net_profit'   => $order->net_profit,
                    'receipt_url'  => route('orders.receipt', $order),
                ]);
            }

            return redirect()->route('orders.receipt', $order)
                             ->with('success', "Order #{$order->order_number} completed!");

        } catch (InsufficientStockException $e) {
            Log::warning('POS: Insufficient stock', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'type'    => 'insufficient_stock',
                ], 422);
            }
            return back()->withErrors(['cart' => $e->getMessage()]);

        } catch (\Throwable $e) {
            Log::error('POS: Order failed', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
            }
            return back()->withErrors(['cart' => 'An unexpected error occurred.']);
        }
    }

    public function receipt(Order $order)
    {
        $order->load('items.menuItem', 'user');
        return view('pos.receipt', compact('order'));
    }

    public function cancel(Order $order)
    {
        abort_if($order->status === 'cancelled', 422, 'Order is already cancelled.');
        $order->update(['status' => 'cancelled']);
        return back()->with('success', "Order #{$order->order_number} cancelled.");
    }
}