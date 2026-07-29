<?php

declare(strict_types=1);

namespace App\Domains\Company\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompanyMembership extends Model
{
    use HasUlids;

    protected $fillable = ['company_id', 'user_id', 'role', 'permissions_json', 'is_active'];

    protected function casts(): array
    {
        return ['permissions_json' => 'array', 'is_active' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
