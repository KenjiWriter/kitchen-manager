<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user who created this group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all members of this group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Check if user is a member of this group.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a user to this group.
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remove a user from this group.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Get user's role in this group.
     */
    public function getMemberRole(User $user): ?string
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        return $member?->pivot->role;
    }

    /**
     * Check if user is owner or admin of this group.
     */
    public function canManage(User $user): bool
    {
        $role = $this->getMemberRole($user);
        return in_array($role, ['owner', 'admin']);
    }
}
