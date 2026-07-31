<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Host\Contracts\NotificationAdapterContract;
use App\Domains\WorkCore\System\Notifications\NotificationDispatcherContract;
use App\Domains\WorkCore\System\Notifications\NotificationIntent;
use App\Titan\Audit\AuditRecorder;
use Illuminate\Support\Facades\Log;

final class MeetupNotificationAdapter implements NotificationAdapterContract, NotificationDispatcherContract
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function dispatch(NotificationIntent $intent): void
    {
        $this->audit->record([
            'company_id' => $intent->companyId,
            'action' => 'workcore.notification.requested',
            'entity_type' => $intent->recipientType,
            'entity_id' => $intent->recipientId,
            'correlation_id' => $intent->correlationId,
            'metadata' => [
                'key' => $intent->key,
                'channel_hints' => $intent->channelHints,
                'idempotency_key' => $intent->idempotencyKey,
                'payload_keys' => array_keys($intent->payload),
            ],
        ]);

        Log::info('WorkCore notification intent recorded for host delivery.', [
            'company_id' => $intent->companyId,
            'key' => $intent->key,
            'recipient_type' => $intent->recipientType,
            'channel_hints' => $intent->channelHints,
            'correlation_id' => $intent->correlationId,
        ]);
    }
}
