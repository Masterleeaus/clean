<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\WorkCore\DTO;

final readonly class WorkCoreJobResolution
{
    /** @param array<int,WorkCoreJobCandidate> $candidates */
    private function __construct(
        public string $status,
        public ?WorkCoreJobCandidate $job,
        public array $candidates,
        public ?string $message = null,
    ) {}

    public static function found(WorkCoreJobCandidate $job): self
    {
        return new self('found', $job, [$job]);
    }

    /** @param array<int,WorkCoreJobCandidate> $candidates */
    public static function ambiguous(array $candidates): self
    {
        return new self('ambiguous', null, $candidates, 'Several WorkCore jobs match that description.');
    }

    public static function notFound(): self
    {
        return new self('not_found', null, [], 'No matching WorkCore job was found.');
    }

    public static function unavailable(string $message): self
    {
        return new self('unavailable', null, [], $message);
    }
}
