<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── KPI: Today ────────────────────────────────────────────────────────
        $todayStats = Order::completed()
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*)           AS order_count,
                SUM(total_amount)  AS revenue,
                SUM(net_profit)    AS profit
            ')
            ->first();

        $yesterdayRevenue = Order::completed()
            ->whereDate('created_at', $yesterday)
            ->sum('total_amount');

        $revenueChange = $yesterdayRevenue > 0
            ? (($todayStats->revenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : null;

        // ── 7-day trend ───────────────────────────────────────────────────────
        $trend = Order::completed()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(created_at) as date, SUM(total_amount) as revenue, SUM(net_profit) as profit")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in any missing days with zeros
        $trendLabels  = [];
        $trendRevenue = [];
        $trendProfit  = [];

        for ($i = 6; $i >= 0; $i--) {
            $day   = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('D');
            $trendLabels[]  = $label;
            $trendRevenue[] = (float) ($trend[$day]->revenue ?? 0);
            $trendProfit[]  = (float) ($trend[$day]->profit  ?? 0);
        }

        // ── Top 5 selling items (by qty) ──────────────────────────────────────
        $topItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', now()->subDays(29))
            ->selectRaw('
                menu_items.id,
                menu_items.name,
                SUM(order_items.quantity)    AS total_qty,
                SUM(order_items.line_total)  AS total_revenue,
                SUM(order_items.line_profit) AS total_profit
            ')
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── Low stock count ───────────────────────────────────────────────────
        $lowStockCount = Ingredient::whereRaw('quantity_in_stock <= low_stock_threshold')->count();

        // ── Monthly summary ───────────────────────────────────────────────────
        $monthStats = Order::completed()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('COUNT(*) AS order_count, SUM(total_amount) AS revenue, SUM(net_profit) AS profit')
            ->first();

        return view('dashboard', compact(
            'todayStats',
            'revenueChange',
            'trendLabels',
            'trendRevenue',
            'trendProfit',
            'topItems',
            'lowStockCount',
            'monthStats'
        ));
    }
}