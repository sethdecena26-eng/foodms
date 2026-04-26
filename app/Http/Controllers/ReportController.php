<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\Ingredient;
use App\Models\WasteLog;
use App\Models\TemperatureLog;
use App\Models\HaccpChecklist;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ── Sales & Profit ────────────────────────────────────────────────────────

    public function sales(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        $daily = Order::completed()
            ->dateRange($from, $to)
            ->selectRaw("DATE(created_at) AS date, COUNT(*) AS orders,
                SUM(total_amount) AS revenue, SUM(total_cost) AS cost, SUM(net_profit) AS profit")
            ->groupBy('date')->orderBy('date')->get();

        $totals = [
            'revenue' => $daily->sum('revenue'),
            'cost'    => $daily->sum('cost'),
            'profit'  => $daily->sum('profit'),
            'orders'  => $daily->sum('orders'),
            'margin'  => $daily->sum('revenue') > 0
                ? round(($daily->sum('profit') / $daily->sum('revenue')) * 100, 1) : 0,
        ];

        $chartLabels  = $daily->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->all();
        $chartRevenue = $daily->pluck('revenue')->map(fn($v) => round($v, 2))->all();
        $chartProfit  = $daily->pluck('profit')->map(fn($v) => round($v, 2))->all();

        $byPayment = Order::completed()->dateRange($from, $to)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method')->get();

        return view('reports.sales', compact(
            'daily', 'totals', 'chartLabels', 'chartRevenue', 'chartProfit', 'byPayment', 'from', 'to'
        ));
    }

    // ── Ingredient Usage ──────────────────────────────────────────────────────

    public function ingredientUsage(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        $usage = StockMovement::with('ingredient')
            ->where('type', 'sale_deduction')
            ->whereBetween('created_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->selectRaw('ingredient_id, SUM(ABS(quantity_changed)) AS total_used, COUNT(*) AS deduction_count')
            ->groupBy('ingredient_id')->orderByDesc('total_used')->get();

        $usage = $usage->map(function ($row) {
            $ingredient      = $row->ingredient;
            $row->unit       = $ingredient->unit;
            $row->name       = $ingredient->name;
            $row->total_cost = $row->total_used * (float) $ingredient->cost_per_unit;
            return $row;
        });

        $auditTrail = StockMovement::with(['ingredient', 'user'])
            ->whereBetween('created_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->orderByDesc('created_at')->paginate(30);

        return view('reports.ingredient-usage', compact('usage', 'auditTrail', 'from', 'to'));
    }

    // ── Waste Report (NEW) ────────────────────────────────────────────────────

    public function waste(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        // Daily waste loss
        $daily = WasteLog::dateRange($from, $to)
            ->selectRaw('waste_date, waste_type,
                SUM(quantity * cost_per_unit) as total_loss, COUNT(*) as count')
            ->groupBy('waste_date', 'waste_type')
            ->orderBy('waste_date')
            ->get();

        $dailyTotals = $daily->groupBy('waste_date')->map(fn($rows) => $rows->sum('total_loss'));

        // By type totals
        $byType = WasteLog::dateRange($from, $to)
            ->selectRaw('waste_type, COUNT(*) as count, SUM(quantity * cost_per_unit) as total_loss')
            ->groupBy('waste_type')->orderByDesc('total_loss')->get();

        // Top wasted ingredients
        $topWasted = WasteLog::dateRange($from, $to)
            ->selectRaw('item_name, unit, SUM(quantity) as qty, SUM(quantity * cost_per_unit) as loss')
            ->groupBy('item_name', 'unit')->orderByDesc('loss')->limit(10)->get();

        $totalLoss   = WasteLog::dateRange($from, $to)->get()->sum('total_loss');
        $totalOrders = Order::completed()->dateRange($from, $to)->count();
        $totalRevenue= Order::completed()->dateRange($from, $to)->sum('total_amount');
        $wastePct    = $totalRevenue > 0 ? round(($totalLoss / $totalRevenue) * 100, 2) : 0;

        $wasteTypes  = WasteLog::wasteTypeLabels();
        $wasteColors = WasteLog::wasteTypeColors();

        // Chart — daily totals
        $chartLabels = [];
        $chartData   = [];
        $start = Carbon::parse($from);
        $end   = Carbon::parse($to);
        while ($start->lte($end)) {
            $chartLabels[] = $start->format('M d');
            $chartData[]   = round((float)($dailyTotals[$start->format('Y-m-d')] ?? 0), 2);
            $start->addDay();
        }

        return view('reports.waste', compact(
            'from', 'to', 'byType', 'topWasted',
            'totalLoss', 'wastePct', 'totalRevenue',
            'wasteTypes', 'wasteColors',
            'chartLabels', 'chartData'
        ));
    }

    // ── Food Safety Report (NEW) ──────────────────────────────────────────────

    public function foodSafety(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        // Temperature compliance
        $tempTotal    = TemperatureLog::whereBetween('log_date', [$from, $to])->count();
        $tempOutRange = TemperatureLog::whereBetween('log_date', [$from, $to])
            ->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius')
            ->count();
        $tempCompliance = $tempTotal > 0 ? round((($tempTotal - $tempOutRange) / $tempTotal) * 100, 1) : 100;

        // Out of range logs
        $outOfRangeLogs = TemperatureLog::with('user')
            ->whereBetween('log_date', [$from, $to])
            ->whereRaw('temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius')
            ->orderByDesc('log_date')
            ->get();

        // Temperature by location
        $byLocation = TemperatureLog::whereBetween('log_date', [$from, $to])
            ->selectRaw('location,
                COUNT(*) as total,
                SUM(CASE WHEN temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius THEN 1 ELSE 0 END) as alerts,
                AVG(temperature_celsius) as avg_temp')
            ->groupBy('location')
            ->orderByDesc('alerts')
            ->get();

        // HACCP compliance
        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $haccpPossible  = $days * 2;
        $haccpCompleted = HaccpChecklist::whereBetween('checklist_date', [$from, $to])
            ->where('status', 'completed')->count();
        $haccpMissed    = $haccpPossible - $haccpCompleted;
        $haccpRate      = $haccpPossible > 0 ? round(($haccpCompleted / $haccpPossible) * 100, 1) : 0;

        // HACCP history
        $haccpHistory = HaccpChecklist::with('user')
            ->whereBetween('checklist_date', [$from, $to])
            ->orderByDesc('checklist_date')
            ->orderBy('shift_type')
            ->get()
            ->groupBy('checklist_date');

        return view('reports.food-safety', compact(
            'from', 'to',
            'tempTotal', 'tempOutRange', 'tempCompliance',
            'outOfRangeLogs', 'byLocation',
            'haccpRate', 'haccpCompleted', 'haccpMissed', 'haccpPossible',
            'haccpHistory'
        ));
    }
}