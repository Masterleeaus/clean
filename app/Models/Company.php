<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Company extends Model
{
    use HasFactory;

    protected $table = 'tz_companies';

    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'status',
        'timezone',
        'currency',
        'country_code',
        'settings',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class, 'company_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tz_company_memberships', 'company_id', 'user_id')
            ->withPivot(['id', 'role_id', 'role_key', 'status', 'is_owner'])
            ->withTimestamps();
    }
}
