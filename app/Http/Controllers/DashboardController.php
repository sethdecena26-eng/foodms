<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ingredient;
use App\Models\OrderItem;
use App\Models\WasteLog;
use App\Models\TemperatureLog;
use App\Models\HaccpChecklist;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── Sales KPIs ────────────────────────────────────────────────────────
        $todayStats = Order::completed()
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) AS order_count, SUM(total_amount) AS revenue, SUM(net_profit) AS profit')
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

        $trendLabels = $trendRevenue = $trendProfit = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $trendLabels[]  = now()->subDays($i)->format('D');
            $trendRevenue[] = (float) ($trend[$day]->revenue ?? 0);
            $trendProfit[]  = (float) ($trend[$day]->profit  ?? 0);
        }

        // ── Top 5 selling items ───────────────────────────────────────────────
        $topItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', now()->subDays(29))
            ->selectRaw('menu_items.id, menu_items.name,
                SUM(order_items.quantity) AS total_qty,
                SUM(order_items.line_total) AS total_revenue,
                SUM(order_items.line_profit) AS total_profit')
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── Low stock ─────────────────────────────────────────────────────────
        $lowStockCount = Ingredient::whereRaw('quantity_in_stock <= low_stock_threshold')->count();

        // ── Monthly summary ───────────────────────────────────────────────────
        $monthStats = Order::completed()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('COUNT(*) AS order_count, SUM(total_amount) AS revenue, SUM(net_profit) AS profit')
            ->first();

        // ── Waste KPIs (today + this month) ──────────────────────────────────
        $todayWasteLoss = WasteLog::where('waste_date', $today->format('Y-m-d'))
            ->get()->sum('total_loss');

        $monthWasteLoss = WasteLog::whereMonth('waste_date', now()->month)
            ->whereYear('waste_date', now()->year)
            ->get()->sum('total_loss');

        // ── Temperature alerts (today) ────────────────────────────────────────
        $tempAlerts = TemperatureLog::where('log_date', $today->format('Y-m-d'))
            ->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius')
            ->count();

        $tempLogsToday = TemperatureLog::where('log_date', $today->format('Y-m-d'))->count();

        // ── HACCP compliance (last 7 days) ────────────────────────────────────
        // Each day has 2 possible checklists (opening + closing) = 14 total possible
        $haccpCompleted = HaccpChecklist::where('checklist_date', '>=', now()->subDays(6)->format('Y-m-d'))
            ->where('status', 'completed')
            ->count();
        $haccpPossible  = 7 * 2; // 7 days × 2 shifts
        $haccpRate      = round(($haccpCompleted / $haccpPossible) * 100);

        // Today's checklists
        $todayOpeningDone = HaccpChecklist::where('checklist_date', $today->format('Y-m-d'))
            ->where('shift_type', 'opening')->where('status', 'completed')->exists();
        $todayClosingDone = HaccpChecklist::where('checklist_date', $today->format('Y-m-d'))
            ->where('shift_type', 'closing')->where('status', 'completed')->exists();

        return view('dashboard', compact(
            'todayStats', 'revenueChange',
            'trendLabels', 'trendRevenue', 'trendProfit',
            'topItems', 'lowStockCount', 'monthStats',
            'todayWasteLoss', 'monthWasteLoss',
            'tempAlerts', 'tempLogsToday',
            'haccpRate', 'todayOpeningDone', 'todayClosingDone'
        ));
    }
}