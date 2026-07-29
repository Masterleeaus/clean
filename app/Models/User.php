<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'active_company_id',
        'email',
        'password',
        'avatar',
        'phone',
        'notifications_muted',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active_company_id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_muted' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }


    public function activeCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'active_company_id');
    }

    public function companyMemberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class, 'user_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'tz_company_memberships', 'user_id', 'company_id')
            ->withPivot(['id', 'role_id', 'role_key', 'status', 'is_owner'])
            ->withTimestamps();
    }

    /**
     * Check if user is online (active within last 5 minutes).
     */
    public function isOnline(): bool
    {
        if (! $this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Get the conversations that the user is part of.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants')
            ->withTimestamps()
            ->withPivot('last_read_at');
    }

    /**
     * Get the participant records for this user.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
