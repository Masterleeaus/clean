<?php

declare(strict_types=1);

namespace TitanZero\Interaction\Wizard\Command;

use TitanZero\Interaction\Wizard\WizardSession;

final class CommandMapper
{
    public function map(WizardSession $session): array
    {
        $context = $session->context;
        return [
            'id' => self::uuid(),
            'capability' => $session->definition->capability,
            'payload' => $session->data,
            'metadata' => [
                'wizard_id' => $session->definition->id,
                'wizard_version' => $session->definition->version,
                'session_id' => $session->id,
                'tenant_id' => (string) ($context['tenant_id'] ?? ''),
                'user_id' => $context['user_id'] ?? null,
                'device_id' => (string) ($context['device_id'] ?? ''),
                'created_at' => gmdate(DATE_ATOM),
                'created_via' => 'universal_wizard',
            ],
        ];
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
