<?php

declare(strict_types=1);

namespace App\Domains\Company\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Company extends Model
{
    use HasUlids;

    protected $fillable = [
        'name', 'legal_name', 'trading_name', 'abn', 'gst_registered',
        'currency_code', 'timezone', 'email', 'phone', 'address_json', 'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'gst_registered' => 'boolean',
            'address_json' => 'array',
            'settings_json' => 'array',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_memberships')
            ->withPivot(['role', 'permissions_json', 'is_active'])
            ->withTimestamps();
    }
}
