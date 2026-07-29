<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\WorkCore\DTO;

final readonly class WorkCoreJobCandidate
{
    public function __construct(
        public string $publicId,
        public string $number,
        public string $title,
        public string $status,
        public string $customerName,
        public ?string $premisesName,
    ) {}

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'public_id' => $this->publicId,
            'number' => $this->number,
            'title' => $this->title,
            'status' => $this->status,
            'customer_name' => $this->customerName,
            'premises_name' => $this->premisesName,
        ];
    }
}
