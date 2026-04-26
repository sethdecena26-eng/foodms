<?php

namespace App\Http\Controllers;

use App\Models\WasteLog;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        $logs = WasteLog::with('user', 'ingredient')
            ->dateRange($from, $to)
            ->latest('waste_date')
            ->paginate(25)
            ->withQueryString();

        // Summary by type
        $byType = WasteLog::dateRange($from, $to)
            ->selectRaw('waste_type, COUNT(*) as count, SUM(quantity * cost_per_unit) as total_loss')
            ->groupBy('waste_type')
            ->orderByDesc('total_loss')
            ->get();

        // Daily loss trend (last 30 days)
        $trend = WasteLog::where('waste_date', '>=', now()->subDays(29)->format('Y-m-d'))
            ->selectRaw('waste_date, SUM(quantity * cost_per_unit) as daily_loss')
            ->groupBy('waste_date')
            ->orderBy('waste_date')
            ->get()
            ->keyBy('waste_date');

        // KPIs
        $totalLoss    = WasteLog::dateRange($from, $to)->get()->sum('total_loss');
        $expiredLoss  = WasteLog::dateRange($from, $to)->where('waste_type', 'expired')->get()->sum('total_loss');
        $entryCount   = WasteLog::dateRange($from, $to)->count();

        // Top wasted ingredients
        $topWasted = WasteLog::dateRange($from, $to)
            ->selectRaw('item_name, SUM(quantity * cost_per_unit) as loss, SUM(quantity) as qty, unit')
            ->groupBy('item_name', 'unit')
            ->orderByDesc('loss')
            ->limit(5)
            ->get();

        $ingredients  = Ingredient::orderBy('name')->get();
        $wasteTypes   = WasteLog::wasteTypeLabels();
        $wasteColors  = WasteLog::wasteTypeColors();

        // Fill trend chart
        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartData[]   = round((float) ($trend[$day]->daily_loss ?? 0), 2);
        }

        return view('waste.index', compact(
            'logs', 'from', 'to', 'byType',
            'totalLoss', 'expiredLoss', 'entryCount',
            'topWasted', 'ingredients', 'wasteTypes', 'wasteColors',
            'chartLabels', 'chartData'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingredient_id'    => 'nullable|exists:ingredients,id',
            'waste_type'       => 'required|in:expired,spoilage,overproduction,cooking_error,other',
            'item_name'        => 'required|string|max:150',
            'quantity'         => 'required|numeric|min:0.001',
            'unit'             => 'required|string|max:30',
            'cost_per_unit'    => 'required|numeric|min:0',
            'waste_date'       => 'required|date',
            'reason'           => 'nullable|string|max:500',
            'preventive_action'=> 'nullable|string|max:500',
        ]);

        $data['user_id'] = auth()->id();

        WasteLog::create($data);

        return back()->with('success', 'Waste entry recorded. Loss: ₱' .
            number_format($data['quantity'] * $data['cost_per_unit'], 2));
    }

    public function destroy(WasteLog $wasteLog)
    {
        $wasteLog->delete();
        return back()->with('success', 'Waste entry removed.');
    }
}
