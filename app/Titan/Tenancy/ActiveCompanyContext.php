<?php

declare(strict_types=1);

namespace App\Titan\Tenancy;

use JsonSerializable;

final readonly class ActiveCompanyContext implements JsonSerializable
{
    public function __construct(
        public int $companyId,
        public int $userId,
        public int $membershipId,
        public string $roleKey,
        public bool $isOwner,
    ) {
        if ($companyId < 1 || $userId < 1 || $membershipId < 1) {
            throw new \InvalidArgumentException('Company, user and membership identifiers must be positive integers.');
        }
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    /** @return array{company_id:int,user_id:int,membership_id:int,role_key:string,is_owner:bool} */
    public function jsonSerialize(): array
    {
        return [
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'membership_id' => $this->membershipId,
            'role_key' => $this->roleKey,
            'is_owner' => $this->isOwner,
        ];
    }
}
