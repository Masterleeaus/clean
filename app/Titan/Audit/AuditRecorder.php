<?php

declare(strict_types=1);

namespace App\Titan\Audit;

interface AuditRecorder
{
    /** @param array<string,mixed> $record */
    public function record(array $record): string;
}
