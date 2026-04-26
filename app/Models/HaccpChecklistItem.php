<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HaccpChecklistItem extends Model
{
    protected $table = 'haccp_checklist_items';

    protected $fillable = [
        'haccp_checklist_id', 'haccp_item_id', 'status', 'notes',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(HaccpChecklist::class, 'haccp_checklist_id');
    }

    public function haccpItem(): BelongsTo
    {
        return $this->belongsTo(HaccpItem::class, 'haccp_item_id');
    }
}
