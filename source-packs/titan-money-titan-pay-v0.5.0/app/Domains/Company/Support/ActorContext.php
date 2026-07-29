<?php

declare(strict_types=1);

namespace App\Domains\Company\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ActorContext
{
    private const MAX_CONTEXT_VALUE_LENGTH = 255;

    private string $actorType = 'system';
    private ?string $userId = null;
    private ?string $agentId = null;
    private ?string $deviceId = null;
    private ?string $conversationId = null;
    private ?string $correlationId = null;
    private ?string $causationId = null;

    public function setHuman(Request $request): void
    {
        $this->actorType = 'human';
        $this->userId = $request->user()?->getKey() !== null ? (string) $request->user()->getKey() : null;
        $this->agentId = null;
        $this->deviceId = $this->normaliseContextValue($request->header('X-Device-Id'));
        $this->conversationId = $this->normaliseContextValue($request->header('X-Conversation-Id'));
        $this->correlationId = $this->normaliseUlid($request->header('X-Correlation-Id')) ?? (string) Str::ulid();
        $this->causationId = $this->normaliseUlid($request->header('X-Causation-Id'));
    }


    public function setHumanForConversation(\App\Models\User $user, string|int $conversationId, ?string $correlationId = null, ?string $causationId = null): void
    {
        $this->actorType = 'human';
        $this->userId = (string) $user->getKey();
        $this->agentId = null;
        $this->deviceId = null;
        $this->conversationId = $this->normaliseContextValue($conversationId);
        $this->correlationId = $this->normaliseUlid($correlationId) ?? (string) Str::ulid();
        $this->causationId = $this->normaliseUlid($causationId);
    }

    public function setAgent(string $agentId, ?string $correlationId = null, ?string $causationId = null, string|int|null $conversationId = null): void
    {
        $this->actorType = 'agent';
        $this->userId = null;
        $this->agentId = $this->normaliseContextValue($agentId) ?? 'unknown-agent';
        $this->deviceId = null;
        $this->conversationId = $this->normaliseContextValue($conversationId);
        $this->correlationId = $this->normaliseUlid($correlationId) ?? (string) Str::ulid();
        $this->causationId = $this->normaliseUlid($causationId);
    }

    public function setSystem(?string $correlationId = null): void
    {
        $this->actorType = 'system';
        $this->userId = null;
        $this->agentId = null;
        $this->deviceId = null;
        $this->conversationId = null;
        $this->correlationId = $this->normaliseUlid($correlationId) ?? (string) Str::ulid();
        $this->causationId = null;
    }

    public function actorType(): string { return $this->actorType; }
    public function userId(): ?string { return $this->userId; }
    public function agentId(): ?string { return $this->agentId; }
    public function actorId(): ?string { return $this->agentId ?: $this->userId; }
    public function deviceId(): ?string { return $this->deviceId; }
    public function conversationId(): ?string { return $this->conversationId; }

    public function correlationId(): string
    {
        if ($this->correlationId === null) {
            $this->correlationId = (string) Str::ulid();
        }

        return $this->correlationId;
    }

    public function causationId(): ?string { return $this->causationId; }

    private function normaliseContextValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalised = trim((string) $value);
        if ($normalised === '') {
            return null;
        }

        return substr($normalised, 0, self::MAX_CONTEXT_VALUE_LENGTH);
    }

    private function normaliseUlid(mixed $value): ?string
    {
        $normalised = $this->normaliseContextValue($value);

        return $normalised !== null && Str::isUlid($normalised) ? $normalised : null;
    }
}
