<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\WorkCore\DTO;

final readonly class CompletedBillableJob
{
    /** @param array<int,array<string,mixed>> $services @param array<string,mixed> $customer @param array<string,mixed>|null $premises */
    public function __construct(
        public string $publicId,
        public string $number,
        public string $title,
        public string $status,
        public string $sourceVersion,
        public array $customer,
        public ?array $premises,
        public array $services,
        public ?string $completedAt,
    ) {}
}
