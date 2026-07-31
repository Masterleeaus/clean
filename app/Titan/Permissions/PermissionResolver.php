<?php

declare(strict_types=1);

namespace App\Titan\Permissions;

use App\Models\CompanyMembership;
use Illuminate\Support\Facades\DB;

final class PermissionResolver
{
    public function allows(int $userId, int $companyId, string $permission): bool
    {
        $membership = CompanyMembership::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->first();

        if ($membership === null) {
            return false;
        }

        if ($membership->is_owner || $membership->role_key === 'owner') {
            return true;
        }

        $memberLevel = DB::table('tz_company_member_permissions')
            ->where('company_id', $companyId)
            ->where('membership_id', $membership->getKey())
            ->where('permission', $permission)
            ->value('access_level');

        if (is_string($memberLevel)) {
            return in_array($memberLevel, ['all', 'manage', 'write', 'read'], true);
        }

        if ($membership->role_id === null) {
            return false;
        }

        $roleLevel = DB::table('tz_company_role_permissions')
            ->where('company_id', $companyId)
            ->where('role_id', $membership->role_id)
            ->where('permission', $permission)
            ->value('access_level');

        return is_string($roleLevel)
            && in_array($roleLevel, ['all', 'manage', 'write', 'read'], true);
    }
}
