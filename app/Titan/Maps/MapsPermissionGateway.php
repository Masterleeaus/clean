<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\PermissionAuthorizer;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class MapsPermissionGateway implements PermissionAuthorizer
{
    public function __construct(
        private readonly PermissionResolver $permissions,
        private readonly ActiveCompanyContext $context,
    ) {}

    public function authorize(string $userId, string $companyId, string $permission, array $context = []): void
    {
        if (! is_numeric($userId) || ! is_numeric($companyId)) {
            throw new AccessDeniedHttpException('A valid Titan user and company context is required.');
        }

        $resolvedUser = (int) $userId;
        $resolvedCompany = (int) $companyId;
        if ($resolvedUser !== $this->context->userId || $resolvedCompany !== $this->context->companyId) {
            throw new AccessDeniedHttpException('Maps access does not match the active Titan company context.');
        }

        if (! $this->permissions->allows($resolvedUser, $resolvedCompany, $permission)) {
            throw new AccessDeniedHttpException('You are not permitted to perform this Maps Intelligence operation.');
        }
    }
}
