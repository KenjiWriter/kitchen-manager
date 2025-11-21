<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PantryItem extends Model
{
    protected $fillable = [
        'product_id',
        'added_by',
        'user_group_id',
        'expiry_date',
        'quantity',
        'location',
        'notes',
        'consumed_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'consumed_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * Get the product for this pantry item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who added this item.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the group this item belongs to.
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Check if item is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if item is expiring soon (within 3 days).
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        $today = Carbon::today();
        $expiryDate = Carbon::parse($this->expiry_date);
        
        return $expiryDate->isAfter($today) && $expiryDate->diffInDays($today) <= 3;
    }

    /**
     * Get days until expiry.
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::today()->diffInDays($this->expiry_date, false);
    }

    /**
     * Mark item as consumed.
     */
    public function markAsConsumed(): void
    {
        $this->consumed_at = now();
        $this->save();
    }

    /**
     * Check if item is active (not consumed).
     */
    public function isActive(): bool
    {
        return $this->consumed_at === null;
    }

    /**
     * Scope: only active items (not consumed).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('consumed_at');
    }

    /**
     * Scope: items expiring soon.
     */
    public function scopeExpiringSoon($query)
    {
        $threeDaysFromNow = Carbon::today()->addDays(3);
        
        return $query->active()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $threeDaysFromNow)
            ->whereDate('expiry_date', '>=', Carbon::today());
    }

    /**
     * Scope: expired items.
     */
    public function scopeExpired($query)
    {
        return $query->active()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::today());
    }

    /**
     * Scope: filter by user group.
     */
    public function scopeForGroup($query, $groupId)
    {
        return $query->where('user_group_id', $groupId);
    }
}
