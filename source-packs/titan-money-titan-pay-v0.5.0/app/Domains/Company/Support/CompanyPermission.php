<?php

declare(strict_types=1);

namespace App\Domains\Company\Support;

use App\Domains\Company\Models\CompanyMembership;
use App\Models\User;

final class CompanyPermission
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function allows(User $user, string $permission): bool
    {
        $companyId = $this->currentCompany->id();
        if (! $companyId) return false;

        $membership = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->getKey())
            ->where('is_active', true)
            ->first();

        if (! $membership) return false;
        if (in_array($membership->role, ['owner', 'admin'], true)) return true;

        $grants = $membership->permissions_json ?? [];
        if (in_array('*', $grants, true) || in_array($permission, $grants, true)) return true;

        foreach ($grants as $grant) {
            if (is_string($grant) && str_ends_with($grant, '.*') && str_starts_with($permission, substr($grant, 0, -1))) {
                return true;
            }
        }

        return false;
    }
}
