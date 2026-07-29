<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Authorization\WorkCoreAccessLevel;
use App\Domains\WorkCore\System\Contracts\PermissionResolverContract;
use App\Titan\Permissions\PermissionResolver;

final class MeetupPermissionResolver implements PermissionResolverContract
{
    public function __construct(private readonly PermissionResolver $permissions) {}

    public function accessLevel(int|string $userId, int $companyId, string $permission): WorkCoreAccessLevel
    {
        if (! is_numeric($userId) || (int) $userId < 1 || $companyId < 1 || trim($permission) === '') {
            return WorkCoreAccessLevel::None;
        }

        return $this->permissions->allows((int) $userId, $companyId, $permission)
            ? WorkCoreAccessLevel::All
            : WorkCoreAccessLevel::None;
    }

    public function allows(int|string $userId, int $companyId, string $permission): bool
    {
        return $this->accessLevel($userId, $companyId, $permission)->grantsAnyAccess();
    }
}
