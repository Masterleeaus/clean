<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Contracts\TenantResolverContract;
use App\Titan\Tenancy\CompanyContextResolver;
use Illuminate\Http\Request;

final class MeetupTenantResolver implements TenantResolverContract
{
    public function __construct(private readonly CompanyContextResolver $resolver) {}

    public function resolve(Request $request): ?array
    {
        $context = $this->resolver->resolve($request);

        return $context === null ? null : [
            'company_id' => $context->companyId,
            'user_id' => $context->userId,
        ];
    }
}
