<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'avatar',
        'phone',
        'notifications_muted',
        'last_seen_at',
        'active_company_id',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_muted' => 'boolean',
            'last_seen_at' => 'datetime',
            'is_platform_admin' => 'boolean',
        ];
    }


    public function companyMemberships()
    {
        return $this->hasMany(\App\Domains\Company\Models\CompanyMembership::class);
    }

    public function companies()
    {
        return $this->belongsToMany(
            \App\Domains\Company\Models\Company::class,
            'company_memberships'
        )->withPivot(['role', 'permissions_json', 'is_active'])->withTimestamps();
    }

    public function activeCompany()
    {
        return $this->belongsTo(\App\Domains\Company\Models\Company::class, 'active_company_id');
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
