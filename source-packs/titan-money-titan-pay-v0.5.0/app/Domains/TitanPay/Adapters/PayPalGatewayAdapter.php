<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Adapters;

use App\Domains\TitanPay\Contracts\GatewayAdapter;
use App\Domains\TitanPay\Contracts\SecretResolver;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\Support\PayPalWebhookParser;
use App\Domains\TitanPay\ValueObjects\GatewayInitiation;
use App\Domains\TitanPay\ValueObjects\WebhookVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PayPalGatewayAdapter implements GatewayAdapter
{
    public function __construct(
        private readonly SecretResolver $secrets,
        private readonly PayPalWebhookParser $parser,
    ) {}

    public function name(): string
    {
        return 'paypal';
    }

    public function isAvailable(?GatewayConnection $connection = null): bool
    {
        if (! $connection?->vault_secret_reference) {
            return false;
        }

        $secret = $this->secrets->resolve($connection->vault_secret_reference);
        return isset($secret['client_id'], $secret['client_secret'], $secret['webhook_id']);
    }

    private function base(?GatewayConnection $connection): string
    {
        return $connection?->environment === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function token(GatewayConnection $connection): string
    {
        $secret = $this->secrets->resolve($connection->vault_secret_reference);
        $response = Http::asForm()
            ->withBasicAuth((string) ($secret['client_id'] ?? ''), (string) ($secret['client_secret'] ?? ''))
            ->post($this->base($connection).'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal authentication failed.');
        }

        return (string) $response->json('access_token');
    }

    public function initiate(CollectionSession $session, ?GatewayConnection $connection = null): GatewayInitiation
    {
        if (! $connection) {
            throw new RuntimeException('PayPal gateway connection is required.');
        }

        $response = Http::withToken($this->token($connection))
            ->withHeaders(['PayPal-Request-Id' => 'titanpay-'.$session->id])
            ->post($this->base($connection).'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $session->reference,
                    'custom_id' => $session->id,
                    'amount' => [
                        'currency_code' => $session->currency_code,
                        'value' => number_format($session->amount_minor / 100, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('titanpay.public.return', [
                        'token' => $session->getAttribute('public_token_once'),
                        'provider' => 'paypal',
                    ]),
                    'cancel_url' => route('titanpay.public.show', [
                        'token' => $session->getAttribute('public_token_once'),
                    ]),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal order creation failed.');
        }

        $data = $response->json();
        $approvalUrl = null;
        foreach ($data['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                $approvalUrl = $link['href'];
                break;
            }
        }
        if (! is_string($approvalUrl) || $approvalUrl === '') {
            throw new RuntimeException('PayPal did not return an approval URL.');
        }

        return new GatewayInitiation('processing', (string) $data['id'], $approvalUrl, raw: $data);
    }

    /** Capture does not mark Titan Money paid; the verified PayPal webhook remains authoritative. */
    public function captureOrder(string $orderId, GatewayConnection $connection): array
    {
        $response = Http::withToken($this->token($connection))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->send('POST', $this->base($connection).'/v2/checkout/orders/'.rawurlencode($orderId).'/capture', [
                'body' => '{}',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal order capture failed.');
        }

        return $response->json();
    }

    public function verifyWebhook(Request $request, ?GatewayConnection $connection = null): WebhookVerification
    {
        if (! $connection) {
            return new WebhookVerification(false, 'missing-connection', 'unknown', failureReason: 'Gateway connection not found.');
        }

        $secret = $this->secrets->resolve($connection->vault_secret_reference);
        $data = $request->json()->all();
        $verify = Http::withToken($this->token($connection))
            ->post($this->base($connection).'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $secret['webhook_id'] ?? null,
                'webhook_event' => $data,
            ]);

        $valid = $verify->successful() && $verify->json('verification_status') === 'SUCCESS';
        $parsed = $this->parser->parse($data);

        return new WebhookVerification(
            $valid,
            $parsed['event_id'],
            $parsed['event_type'],
            $parsed['session_reference'],
            $parsed['transaction_id'],
            $parsed['amount_minor'],
            $parsed['currency_code'],
            $valid ? $parsed['status'] : 'ignored',
            $data,
            $valid ? ($parsed['status'] === 'completed' ? null : 'PayPal event is not a completed payment.') : 'PayPal webhook signature verification failed.',
        );
    }

    public function refund(
        string $transactionReference,
        int $amountMinor,
        string $currencyCode,
        ?GatewayConnection $connection = null,
    ): array {
        if (! $connection) {
            throw new RuntimeException('PayPal gateway connection is required.');
        }

        $response = Http::withToken($this->token($connection))
            ->post($this->base($connection).'/v2/payments/captures/'.rawurlencode($transactionReference).'/refund', [
                'amount' => [
                    'value' => number_format($amountMinor / 100, 2, '.', ''),
                    'currency_code' => $currencyCode,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal refund failed.');
        }

        return $response->json();
    }
}
