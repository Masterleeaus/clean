<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\InvoiceFollowUp;
use App\Domains\TitanPay\Services\CollectionService;
use App\Domains\TitanPay\Services\QrCodeService;
use App\Domains\TitanPay\Support\PaymentMethodOrder;
use Throwable;

final class ChannelDeliveryService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly FollowUpContentFactory $content,
        private readonly CollectionService $collections,
        private readonly QrCodeService $qrCodes,
        private readonly PaymentMethodOrder $paymentMethods,
        private readonly CustomerChannelPreferenceResolver $preferences,
        private readonly DeliveryWindowPolicy $deliveryWindow,
    ) {}

    public function queueIssued(Invoice $invoice, string $eventId): int
    {
        return $this->queue(
            $invoice,
            null,
            'issued',
            'invoice_issued',
            'friendly',
            0,
            ['customer_app','sms','email'],
            'invoice-issued:'.$invoice->id,
        );
    }

    public function queueReminder(Invoice $invoice, string $stageKey, string $templateKey, string $channel, string $eventId, ?string $reminderId = null): int
    {
        return $this->queue($invoice, null, $stageKey, $templateKey, 'friendly', 0, [$channel], 'reminder:'.($reminderId ?: $eventId), $reminderId);
    }

    /** @param array<int,string> $channels */
    public function queueFollowUp(InvoiceFollowUp $followUp, array $channels, string $templateKey): int
    {
        return $this->queue(
            $followUp->invoice,
            $followUp,
            $followUp->stage_key,
            $templateKey,
            $followUp->tone,
            $followUp->days_overdue,
            $channels,
            'follow-up:'.$followUp->id,
        );
    }

    /** @param array<int,string> $channels */
    private function queue(Invoice $invoice, ?InvoiceFollowUp $followUp, string $stageKey, string $templateKey, string $tone, int $daysOverdue, array $channels, string $idempotencyPrefix, ?string $reminderId = null): int
    {
        $this->currentCompany->require();
        $invoice->loadMissing('customer');
        $content = $this->content->make($invoice, $templateKey, $tone, $daysOverdue);
        $requested = array_values(array_unique(array_filter($channels, 'is_string')));
        $customerChannels = $this->preferences->resolve($invoice->customer ?? ($invoice->customer_snapshot_json ?? []), $requested);
        $channels = array_values(array_unique([
            ...$customerChannels,
            ...array_values(array_filter($requested, static fn (string $channel): bool => $channel === 'internal')),
        ]));
        $deliveryExceptionReason = $customerChannels === []
            && array_filter($requested, static fn (string $channel): bool => $channel !== 'internal') !== []
                ? 'No consented or configured customer delivery channel was available. Staff follow-up is required.'
                : null;

        if ($channels === [] && $deliveryExceptionReason !== null) {
            $channels = ['internal'];
        }
        if ($channels === []) {
            return 0;
        }

        $paymentUrl = $qrUrl = null;
        $methods = [];
        try {
            if ($invoice->balance_due_minor > 0) {
                $collection = $this->collections->createForInvoice($invoice, $this->paymentMethods->customerDefault(), 60 * 24 * 14);
                $paymentUrl = route('titanpay.public.show', ['token'=>$collection['token']]);
                $qrUrl = $this->qrCodes->imageUrl($collection['token']);
                $this->qrCodes->record($collection['session'], $collection['token']);
                $methods = $collection['session']->allowed_methods_json ?? [];
            }
        } catch (Throwable $error) {
            report($error);
            $paymentUrl = $qrUrl = null;
            $methods = [];
            $channels = ['internal'];
            $deliveryExceptionReason = 'payment_link_preparation_failed: The secure Titan Pay link or QR code could not be prepared. Staff action is required.';
        }

        $queued = 0;
        foreach ($channels as $channel) {
            $idempotency = implode(':', [$idempotencyPrefix, $channel, $stageKey]);
            if (ChannelDelivery::query()->withoutGlobalScope('company')->where('idempotency_key', $idempotency)->exists()) {
                continue;
            }

            $address = match ($channel) {
                'email' => $invoice->customer?->email ?: ($invoice->customer_snapshot_json['email'] ?? null),
                'sms', 'voice', 'whatsapp' => $invoice->customer?->phone ?: ($invoice->customer_snapshot_json['phone'] ?? null),
                'customer_app' => (string) (($invoice->customer?->metadata_json ?? [])['customer_app_user_id'] ?? ''),
                default => null,
            };

            $delivery = ChannelDelivery::query()->firstOrCreate([
                'idempotency_key' => $idempotency,
            ], [
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'follow_up_id' => $followUp?->id,
                'channel' => $channel,
                'recipient_type' => $channel === 'internal' ? 'company_staff' : 'customer',
                'recipient_id' => $invoice->customer_id,
                'recipient_address' => $address !== '' ? $address : null,
                'template_key' => $templateKey,
                'status' => 'queued',
                'payload_json' => [
                    'subject'=>$content['subject'],
                    'body'=>$content['body'],
                    'invoice_number'=>$invoice->invoice_number,
                    'balance_due_minor'=>$invoice->balance_due_minor,
                    'currency_code'=>$invoice->currency_code,
                    'payment_url'=>$paymentUrl,
                    'qr_url'=>$qrUrl,
                    'payment_methods'=>$methods,
                    'payment_method_order'=>['PayID','Bank transfer','Cash','Credit/debit card through PayPal'],
                    'stage_key'=>$stageKey,
                    'tone'=>$tone,
                    'reminder_id'=>$reminderId,
                    'delivery_exception'=>$deliveryExceptionReason,
                ],
                'scheduled_at' => $this->deliveryWindow->nextAllowedAt($this->currentCompany->require(), $channel)->utc(),
            ]);

            if ($delivery->wasRecentlyCreated) {
                $queued++;
            }
        }

        if ($followUp && $queued > 0) {
            $followUp->update(['status'=>'queued','queued_at'=>now()]);
        }

        return $queued;
    }
}
