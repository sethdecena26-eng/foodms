<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ════════════════════════════════════════════════════════
// HaccpChecklist — one submission per shift per day
// ════════════════════════════════════════════════════════
class HaccpChecklist extends Model
{
    protected $table = 'haccp_checklists';

    protected $fillable = [
        'user_id', 'shift_type', 'checklist_date',
        'status', 'supervisor_notes', 'completed_at',
    ];

    protected $casts = [
        'checklist_date' => 'date',
        'completed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HaccpChecklistItem::class);
    }

    // Count of passed / failed / na items
    public function getPassCountAttribute(): int
    {
        return $this->items->where('status', 'pass')->count();
    }

    public function getFailCountAttribute(): int
    {
        return $this->items->where('status', 'fail')->count();
    }

    public function getCompletionPctAttribute(): int
    {
        $total = $this->items->whereIn('status', ['pass', 'fail'])->count();
        return $total > 0 ? (int) round(($total / $this->items->count()) * 100) : 0;
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('checklist_date', $date);
    }
}
