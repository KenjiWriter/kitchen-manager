<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_token',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'auth_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate a unique auth token for the user.
     */
    public function generateAuthToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('auth_token', $token)->exists());

        $this->auth_token = $token;
        $this->save();

        return $token;
    }

    /**
     * Find user by auth token.
     */
    public static function findByAuthToken(string $token): ?self
    {
        return self::where('auth_token', $token)->first();
    }

    /**
     * Get all groups this user belongs to.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get groups where user is owner.
     */
    public function ownedGroups()
    {
        return $this->hasMany(UserGroup::class, 'created_by');
    }

    /**
     * Get user's active group (first group or default group).
     */
    public function getActiveGroup(): ?UserGroup
    {
        return $this->groups()->first();
    }

    /**
     * Check if user belongs to a specific group.
     */
    public function belongsToGroup(UserGroup $group): bool
    {
        return $this->groups()->where('user_group_id', $group->id)->exists();
    }
}
