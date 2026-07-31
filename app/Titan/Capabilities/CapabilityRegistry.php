<?php

declare(strict_types=1);

namespace App\Titan\Capabilities;

use LogicException;

final class CapabilityRegistry
{
    /** @var array<string,array<string,mixed>> */
    private array $definitions = [];

    /** @param array<string,mixed> $definition */
    public function register(array $definition): void
    {
        $id = trim((string) ($definition['id'] ?? $definition['key'] ?? ''));
        if ($id === '') {
            throw new \InvalidArgumentException('A capability id is required.');
        }
        if (isset($this->definitions[$id])) {
            throw new LogicException("Cannot register duplicate capability: {$id}");
        }

        $definition['id'] = $id;
        $this->definitions[$id] = $definition;
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        return $this->definitions[$id] ?? null;
    }

    /** @return array<string,array<string,mixed>> */
    public function all(): array
    {
        return $this->definitions;
    }
}
