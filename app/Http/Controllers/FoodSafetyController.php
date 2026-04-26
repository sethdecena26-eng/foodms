<?php

namespace App\Http\Controllers;

use App\Models\TemperatureLog;
use App\Models\HaccpChecklist;
use App\Models\HaccpChecklistItem;
use App\Models\HaccpItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FoodSafetyController extends Controller
{
    // ── Temperature Logs ──────────────────────────────────────────────────────

    public function temperatureIndex(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $logs = TemperatureLog::with('user')
            ->where('log_date', $date)
            ->orderBy('log_time')
            ->get();

        $outOfRangeCount = $logs->filter(fn($l) => !$l->is_within_range)->count();

        // Last 7 days out-of-range summary for the mini chart
        $trend = TemperatureLog::selectRaw('log_date, COUNT(*) as total,
                SUM(CASE WHEN temperature_celsius NOT BETWEEN min_safe_celsius AND max_safe_celsius THEN 1 ELSE 0 END) as out_of_range')
            ->where('log_date', '>=', now()->subDays(6)->format('Y-m-d'))
            ->groupBy('log_date')
            ->orderBy('log_date')
            ->get()
            ->keyBy('log_date');

        $safeRanges = TemperatureLog::safeRanges();

        return view('food-safety.temperature', compact(
            'logs', 'date', 'outOfRangeCount', 'trend', 'safeRanges'
        ));
    }

    public function storeTemperature(Request $request)
    {
        $data = $request->validate([
            'location'            => 'required|string|max:100',
            'location_type'       => 'required|in:fridge,freezer,hot_hold,other',
            'temperature_celsius' => 'required|numeric|between:-50,200',
            'min_safe_celsius'    => 'required|numeric',
            'max_safe_celsius'    => 'required|numeric|gt:min_safe_celsius',
            'shift'               => 'required|in:opening,midday,closing',
            'log_date'            => 'required|date',
            'log_time'            => 'required',
            'corrective_action'   => 'nullable|string|max:500',
            'notes'               => 'nullable|string|max:300',
        ]);

        $data['user_id'] = auth()->id();

        TemperatureLog::create($data);

        return back()->with('success', 'Temperature log recorded.');
    }

    public function destroyTemperature(TemperatureLog $temperatureLog)
    {
        $temperatureLog->delete();
        return back()->with('success', 'Log entry removed.');
    }

    // ── HACCP Checklists ──────────────────────────────────────────────────────

    public function haccpIndex(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $opening = HaccpChecklist::with('items.haccpItem', 'user')
            ->where('checklist_date', $date)
            ->where('shift_type', 'opening')
            ->first();

        $closing = HaccpChecklist::with('items.haccpItem', 'user')
            ->where('checklist_date', $date)
            ->where('shift_type', 'closing')
            ->first();

        // All active template items grouped by shift
        $openingItems = HaccpItem::active()
            ->whereIn('applies_to', ['opening', 'both'])
            ->get();

        $closingItems = HaccpItem::active()
            ->whereIn('applies_to', ['closing', 'both'])
            ->get();

        // Recent history (last 7 days)
        $history = HaccpChecklist::with('user')
            ->where('checklist_date', '>=', now()->subDays(6)->format('Y-m-d'))
            ->orderByDesc('checklist_date')
            ->orderBy('shift_type')
            ->get()
            ->groupBy('checklist_date');

        return view('food-safety.haccp', compact(
            'date', 'opening', 'closing',
            'openingItems', 'closingItems', 'history'
        ));
    }

    public function storeHaccp(Request $request)
    {
        $data = $request->validate([
            'shift_type'       => 'required|in:opening,closing',
            'checklist_date'   => 'required|date',
            'supervisor_notes' => 'nullable|string|max:500',
            'items'            => 'required|array',
            'items.*.id'       => 'required|exists:haccp_items,id',
            'items.*.status'   => 'required|in:pass,fail,na',
            'items.*.notes'    => 'nullable|string|max:300',
        ]);

        // Upsert the checklist header
        $checklist = HaccpChecklist::updateOrCreate(
            [
                'checklist_date' => $data['checklist_date'],
                'shift_type'     => $data['shift_type'],
            ],
            [
                'user_id'          => auth()->id(),
                'supervisor_notes' => $data['supervisor_notes'] ?? null,
                'status'           => 'completed',
                'completed_at'     => now(),
            ]
        );

        // Upsert each item response
        foreach ($data['items'] as $item) {
            HaccpChecklistItem::updateOrCreate(
                [
                    'haccp_checklist_id' => $checklist->id,
                    'haccp_item_id'      => $item['id'],
                ],
                [
                    'status' => $item['status'],
                    'notes'  => $item['notes'] ?? null,
                ]
            );
        }

        return back()->with('success', ucfirst($data['shift_type']) . ' checklist saved.');
    }

    // ── HACCP Items management (admin) ────────────────────────────────────────

    public function haccpItems()
    {
        $items = HaccpItem::orderBy('sort_order')->orderBy('category')->get();
        return view('food-safety.haccp-items', compact('items'));
    }

    public function storeHaccpItem(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'category'    => 'required|in:personal_hygiene,temperature_control,cross_contamination,cleaning,storage,other',
            'applies_to'  => 'required|in:opening,closing,both',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        HaccpItem::create($data);
        return back()->with('success', 'Checklist item added.');
    }

    public function updateHaccpItem(Request $request, HaccpItem $haccpItem)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'category'    => 'required|in:personal_hygiene,temperature_control,cross_contamination,cleaning,storage,other',
            'applies_to'  => 'required|in:opening,closing,both',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $haccpItem->update($data);
        return back()->with('success', 'Checklist item updated.');
    }

    public function destroyHaccpItem(HaccpItem $haccpItem)
    {
        $haccpItem->update(['is_active' => false]);
        return back()->with('success', 'Item deactivated.');
    }
}
