<?php
// ════════════════════════════════════════════════════════
// app/Models/OrderItem.php
// ════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'quantity',
        'unit_price', 'unit_cost', 'line_total', 'line_cost', 'line_profit',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'unit_cost'   => 'decimal:4',
        'line_total'  => 'decimal:2',
        'line_cost'   => 'decimal:4',
        'line_profit' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}