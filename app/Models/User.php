<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── RBAC Helpers ─────────────────────────────────────────────────────────

    /**
     * Null-safe role check. Always returns false (not true) if role is null.
     */
    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(Role::EMPLOYEE);
    }

    /**
     * Returns the role name for display, or 'No Role Assigned' if null.
     * Safe to call anywhere in Blade without crashing.
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role?->label ?? 'No Role Assigned';
    }

    /**
     * True if this account has no role yet — used to show the
     * "pending approval" dashboard state.
     */
    public function isPending(): bool
    {
        return $this->role_id === null;
    }
}