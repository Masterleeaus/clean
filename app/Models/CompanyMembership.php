<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CompanyMembership extends Model
{
    protected $table = 'tz_company_memberships';

    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
        'role_key',
        'status',
        'is_owner',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(CompanyRole::class, 'role_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(CompanyPermission::class, 'membership_id');
    }
}
