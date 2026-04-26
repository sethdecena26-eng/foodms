<?php

namespace Database\Seeders;

use App\Models\HaccpItem;
use Illuminate\Database\Seeder;

class HaccpItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ── Opening ──────────────────────────────────────────────────────
            ['title' => 'Wash and sanitize hands before starting shift',          'category' => 'personal_hygiene',    'applies_to' => 'opening', 'sort_order' => 1],
            ['title' => 'Check all staff are wearing clean uniforms and hairnets', 'category' => 'personal_hygiene',    'applies_to' => 'opening', 'sort_order' => 2],
            ['title' => 'Record fridge temperatures (must be 1–5°C)',             'category' => 'temperature_control', 'applies_to' => 'opening', 'sort_order' => 3],
            ['title' => 'Record freezer temperatures (must be -18°C or below)',   'category' => 'temperature_control', 'applies_to' => 'opening', 'sort_order' => 4],
            ['title' => 'Check all refrigerated items for use-by dates',          'category' => 'storage',             'applies_to' => 'opening', 'sort_order' => 5],
            ['title' => 'Verify FIFO (first in, first out) stock rotation',       'category' => 'storage',             'applies_to' => 'opening', 'sort_order' => 6],
            ['title' => 'Sanitize prep surfaces and cutting boards',              'category' => 'cleaning',            'applies_to' => 'opening', 'sort_order' => 7],
            ['title' => 'Check raw meat is stored below ready-to-eat foods',      'category' => 'cross_contamination', 'applies_to' => 'opening', 'sort_order' => 8],
            ['title' => 'Inspect sanitizer solution concentration',               'category' => 'cleaning',            'applies_to' => 'opening', 'sort_order' => 9],

            // ── Both shifts ───────────────────────────────────────────────────
            ['title' => 'No staff handling food while sick or with open wounds',  'category' => 'personal_hygiene',    'applies_to' => 'both',    'sort_order' => 10],
            ['title' => 'Hot-hold equipment maintaining 63°C or above',           'category' => 'temperature_control', 'applies_to' => 'both',    'sort_order' => 11],
            ['title' => 'Separate color-coded boards used for raw/cooked items',  'category' => 'cross_contamination', 'applies_to' => 'both',    'sort_order' => 12],

            // ── Closing ───────────────────────────────────────────────────────
            ['title' => 'Label and date all opened/prepared items before storage','category' => 'storage',             'applies_to' => 'closing', 'sort_order' => 13],
            ['title' => 'Deep clean all cooking equipment and surfaces',          'category' => 'cleaning',            'applies_to' => 'closing', 'sort_order' => 14],
            ['title' => 'Dispose of expired or unsafe food items',               'category' => 'storage',             'applies_to' => 'closing', 'sort_order' => 15],
            ['title' => 'Empty and sanitize all waste bins',                     'category' => 'cleaning',            'applies_to' => 'closing', 'sort_order' => 16],
            ['title' => 'Verify fridge/freezer doors are properly sealed',       'category' => 'temperature_control', 'applies_to' => 'closing', 'sort_order' => 17],
            ['title' => 'Mop and sanitize kitchen floor',                        'category' => 'cleaning',            'applies_to' => 'closing', 'sort_order' => 18],
            ['title' => 'Record final closing temperatures for all cold storage', 'category' => 'temperature_control', 'applies_to' => 'closing', 'sort_order' => 19],
        ];

        foreach ($items as $item) {
            HaccpItem::create(array_merge($item, ['is_active' => true, 'description' => null]));
        }
    }
}