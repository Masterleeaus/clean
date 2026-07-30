<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\AuthorisedCompanyContext;
use App\Titan\Tenancy\ActiveCompanyContext;

final class MapsCompanyContext implements AuthorisedCompanyContext
{
    public function __construct(private readonly ActiveCompanyContext $context) {}

    public function companyId(): string
    {
        return (string) $this->context->companyId;
    }

    public function userId(): string
    {
        return (string) $this->context->userId;
    }

    public function branchId(): ?string
    {
        return null;
    }

    public function workspaceId(): ?string
    {
        return null;
    }
}
