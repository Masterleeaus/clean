<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Actions\Contracts\EntitlementResolverContract;
use App\Domains\WorkCore\System\Capabilities\CapabilityRegistry as WorkCoreCapabilityRegistry;
use App\Titan\Capabilities\CapabilityRegistry as HostCapabilityRegistry;

final class MeetupEntitlementResolver implements EntitlementResolverContract
{
    public function __construct(
        private readonly WorkCoreCapabilityRegistry $workCore,
        private readonly HostCapabilityRegistry $host,
    ) {}

    public function allows(int $companyId, ?string $capability): bool
    {
        if ($companyId < 1) {
            return false;
        }

        return $capability === null
            || ($this->workCore->has($capability) && $this->host->has($capability));
    }
}
