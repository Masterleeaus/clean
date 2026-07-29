<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Models\CompanyMember;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TitanTrainAccess
{
    public function __construct(private TenantContextContract $tenant) {}

    public function membership(User $user): CompanyMember
    {
        $membership = CompanyMember::withoutGlobalScopes()
            ->where('company_id', $this->tenant->companyId())
            ->where('user_id', $user->getKey())
            ->where('status', 'active')
            ->first();

        if ($membership === null) {
            throw new AccessDeniedHttpException('Active Titan company membership is required.');
        }

        return $membership;
    }

    public function requireManager(User $user): CompanyMember
    {
        $membership = $this->membership($user);
        $roles = (array) config('titan_train.manager_roles', ['owner', 'admin', 'manager', 'trainer']);
        if (! $membership->is_owner && ! in_array((string) $membership->role_key, $roles, true)) {
            throw new AccessDeniedHttpException('Titan Train management permission is required.');
        }

        return $membership;
    }

    public function isManager(User $user): bool
    {
        try {
            $membership = $this->membership($user);
        } catch (AccessDeniedHttpException) {
            return false;
        }

        return $membership->is_owner || in_array((string) $membership->role_key, (array) config('titan_train.manager_roles', []), true);
    }
}
