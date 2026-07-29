<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompanyMemberPermission extends Model
{
    protected $table = 'tz_company_member_permissions';

    protected $fillable = ['company_id', 'membership_id', 'permission', 'access_level'];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(CompanyMember::class, 'membership_id');
    }
}
