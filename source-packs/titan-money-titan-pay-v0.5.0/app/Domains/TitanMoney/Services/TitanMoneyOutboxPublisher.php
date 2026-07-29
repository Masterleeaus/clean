<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Events\TitanMoneyOutboxPublished;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\InvoiceReminder;
use App\Domains\TitanMoney\Models\TitanMoneyOutboxEvent;
use Illuminate\Support\Facades\DB;
use Throwable;

final class TitanMoneyOutboxPublisher
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly ChannelDeliveryService $deliveries,
    ) {}

    public function publishPending(int $limit = 200): int
    {
        $count = 0;
        $eventIds = TitanMoneyOutboxEvent::query()->withoutGlobalScope('company')
            ->whereNull('published_at')
            ->orderBy('occurred_at')
            ->limit(max(1, min($limit, 1000)))
            ->pluck('id');

        foreach ($eventIds as $eventId) {
            try {
                $published = DB::transaction(function () use ($eventId): bool {
                    $event = TitanMoneyOutboxEvent::query()->withoutGlobalScope('company')
                        ->whereKey($eventId)
                        ->lockForUpdate()
                        ->first();

                    if ($event === null || $event->published_at !== null) {
                        return false;
                    }

                    $this->currentCompany->set($event->company()->withoutGlobalScope('company')->firstOrFail());
                    $this->actorContext->setAgent('titan-money.outbox-agent', (string) $event->correlation_id);
                    $this->publish($event);

                    $event->forceFill([
                        'published_at' => now(),
                        'attempts' => ((int) $event->attempts) + 1,
                        'last_error' => null,
                    ])->save();

                    return true;
                }, 3);

                if ($published) {
                    $count++;
                }
            } catch (Throwable $error) {
                $this->recordFailure((string) $eventId, $error);
            }
        }

        return $count;
    }

    private function publish(TitanMoneyOutboxEvent $event): void
    {
        if ($event->aggregate_type === Invoice::class) {
            $invoice = Invoice::query()->find($event->aggregate_id);
            if ($invoice && $event->event_type === 'invoice.issued' && $this->sendOnIssueEnabled($event->company_id)) {
                $this->deliveries->queueIssued($invoice, $event->id);
            }
            if ($invoice && $event->event_type === 'invoice.reminder_due') {
                $reminderId = $event->payload_json['reminder_id'] ?? null;
                $reminder = $reminderId ? InvoiceReminder::query()->find($reminderId) : null;
                if ($reminder) {
                    if ($reminder->days_offset >= 0 && $this->receivablesFollowUpEnabled($event->company_id)) {
                        $reminder->update([
                            'status' => 'cancelled',
                            'metadata_json' => array_merge($reminder->metadata_json ?? [], [
                                'suppressed_by' => 'titan-money.receivables-agent',
                            ]),
                        ]);
                    } else {
                        $template = match ($reminder->type) {
                            'on_due' => 'invoice_due_today',
                            'after_due' => 'invoice_overdue_friendly',
                            default => 'invoice_reminder',
                        };
                        $this->deliveries->queueReminder(
                            $invoice,
                            'scheduled-'.$reminder->id,
                            $template,
                            $reminder->channel,
                            $event->id,
                            (string) $reminder->id,
                        );
                    }
                }
            }
        }

        event(new TitanMoneyOutboxPublished($event));
    }

    private function recordFailure(string $eventId, Throwable $error): void
    {
        try {
            DB::transaction(function () use ($eventId, $error): void {
                $event = TitanMoneyOutboxEvent::query()->withoutGlobalScope('company')
                    ->whereKey($eventId)
                    ->lockForUpdate()
                    ->first();

                if ($event === null || $event->published_at !== null) {
                    return;
                }

                $event->forceFill([
                    'attempts' => ((int) $event->attempts) + 1,
                    'last_error' => mb_substr($error->getMessage(), 0, 4000),
                ])->save();
            }, 3);
        } catch (Throwable) {
            // Preserve the original publishing failure; the next scheduler run can retry it.
        }
    }

    private function receivablesFollowUpEnabled(string $companyId): bool
    {
        return AutomationPolicy::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('agent_key', 'receivables_follow_up')
            ->where('enabled', true)
            ->exists();
    }

    private function sendOnIssueEnabled(string $companyId): bool
    {
        $policy = AutomationPolicy::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('agent_key', 'invoice_generation')
            ->first();

        if (! $policy) {
            return (bool) config('titanmoney.automation.send_on_issue', true);
        }

        return (bool) (($policy->settings_json ?? [])['send_on_issue'] ?? true);
    }
}
