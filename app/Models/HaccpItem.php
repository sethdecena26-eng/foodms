<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// ════════════════════════════════════════════════════════
// HaccpItem — master checklist template item
// ════════════════════════════════════════════════════════
class HaccpItem extends Model
{
    protected $table = 'haccp_items';

    protected $fillable = [
        'title', 'description', 'category',
        'applies_to', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function checklistItems(): HasMany
    {
        return $this->hasMany(HaccpChecklistItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
