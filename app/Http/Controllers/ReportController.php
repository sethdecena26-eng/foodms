<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ─── Sales & Profit Report ────────────────────────────────────────────────

    public function sales(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        // Daily breakdown
        $daily = Order::completed()
            ->dateRange($from, $to)
            ->selectRaw("
                DATE(created_at)   AS date,
                COUNT(*)           AS orders,
                SUM(total_amount)  AS revenue,
                SUM(total_cost)    AS cost,
                SUM(net_profit)    AS profit
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Totals
        $totals = [
            'revenue' => $daily->sum('revenue'),
            'cost'    => $daily->sum('cost'),
            'profit'  => $daily->sum('profit'),
            'orders'  => $daily->sum('orders'),
            'margin'  => $daily->sum('revenue') > 0
                ? round(($daily->sum('profit') / $daily->sum('revenue')) * 100, 1)
                : 0,
        ];

        // Chart data
        $chartLabels  = $daily->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->all();
        $chartRevenue = $daily->pluck('revenue')->map(fn($v) => round($v, 2))->all();
        $chartProfit  = $daily->pluck('profit')->map(fn($v) => round($v, 2))->all();

        // Payment method breakdown
        $byPayment = Order::completed()
            ->dateRange($from, $to)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method')
            ->get();

        return view('reports.sales', compact(
            'daily', 'totals', 'chartLabels', 'chartRevenue', 'chartProfit',
            'byPayment', 'from', 'to'
        ));
    }

    // ─── Ingredient Usage Report ──────────────────────────────────────────────

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
            ->selectRaw('
                ingredient_id,
                SUM(ABS(quantity_changed)) AS total_used,
                COUNT(*)                   AS deduction_count
            ')
            ->groupBy('ingredient_id')
            ->orderByDesc('total_used')
            ->get();

        // Attach cost data
        $usage = $usage->map(function ($row) {
            $ingredient     = $row->ingredient;
            $row->unit      = $ingredient->unit;
            $row->name      = $ingredient->name;
            $row->total_cost = $row->total_used * (float) $ingredient->cost_per_unit;
            return $row;
        });

        // All movements for the audit trail section
        $auditTrail = StockMovement::with(['ingredient', 'user'])
            ->whereBetween('created_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('reports.ingredient-usage', compact(
            'usage', 'auditTrail', 'from', 'to'
        ));
    }
}