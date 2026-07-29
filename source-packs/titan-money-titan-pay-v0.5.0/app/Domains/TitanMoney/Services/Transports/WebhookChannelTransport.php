<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services\Transports;

use App\Domains\TitanMoney\Contracts\ChannelTransport;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Services\DeliveryReceipt;
use Illuminate\Support\Facades\Http;
use LogicException;

final class WebhookChannelTransport implements ChannelTransport
{
    public function supports(string $channel): bool
    {
        return in_array($channel, ['sms','voice','whatsapp'], true);
    }

    public function send(ChannelDelivery $delivery): DeliveryReceipt
    {
        $settings = config('titan_channels.transports.'.$delivery->channel, []);
        $url = is_string($settings['url'] ?? null) ? trim($settings['url']) : '';
        if ($url === '') {
            throw new LogicException('The '.$delivery->channel.' transport is not configured.');
        }
        if (! $delivery->recipient_address) {
            throw new LogicException('No recipient address is available for '.$delivery->channel.'.');
        }

        $request = Http::acceptJson()->asJson()->timeout((int) ($settings['timeout_seconds'] ?? 10));
        if (! empty($settings['token'])) {
            $request = $request->withToken((string) $settings['token']);
        }
        $payload = is_array($delivery->payload_json) ? $delivery->payload_json : [];
        $body = trim((string) ($payload['body'] ?? 'Invoice update'));
        if (in_array($delivery->channel, ['sms','whatsapp'], true) && ! empty($payload['payment_url'])) {
            $body .= "\n\nPay securely: ".$payload['payment_url'];
        }

        $response = $request->post($url, [
            'idempotency_key' => $delivery->idempotency_key,
            'channel' => $delivery->channel,
            'to' => $delivery->recipient_address,
            'subject' => $payload['subject'] ?? null,
            'body' => $body,
            'payment_url' => $payload['payment_url'] ?? null,
            'qr_url' => $payload['qr_url'] ?? null,
            'receipt_callback_url' => route('api.titanmoney.channel-receipts.store'),
            'metadata' => [
                'delivery_id'=>$delivery->id,
                'invoice_id'=>$delivery->invoice_id,
                'company_id'=>$delivery->company_id,
                'invoice_number'=>$payload['invoice_number'] ?? null,
                'balance_due_minor'=>$payload['balance_due_minor'] ?? null,
                'currency_code'=>$payload['currency_code'] ?? null,
                'payment_methods'=>$payload['payment_methods'] ?? [],
            ],
        ]);

        if (! $response->successful()) {
            throw new LogicException('The '.$delivery->channel.' provider rejected the delivery request.');
        }

        $externalId = $response->json('message_id') ?? $response->json('id') ?? $response->header('X-Message-Id');
        $status = (string) ($response->json('status') ?? 'sent');

        return new DeliveryReceipt(
            provider: (string) ($settings['provider'] ?? 'webhook'),
            externalMessageId: is_scalar($externalId) ? (string) $externalId : null,
            status: $status,
            raw: (array) ($response->json() ?? []),
            delivered: in_array($status, ['delivered','completed'], true),
        );
    }
}
