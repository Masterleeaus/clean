<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanPay\Adapters\PayPalGatewayAdapter;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\Policies\CollectionEligibility;
use App\Domains\TitanPay\Support\PaymentMethodOrder;
use App\Domains\TitanPay\ValueObjects\GatewayInitiation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;

final class CollectionService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly GatewayRegistry $gateways,
        private readonly CollectionEligibility $eligibility,
        private readonly PaymentMethodOrder $methodOrder,
    ) {}

    /** @return array{session:CollectionSession,token:string} */
    public function createForInvoice(
        Invoice $invoice,
        array $methods = [],
        int $ttlMinutes = 1440,
    ): array {
        $company = $this->currentCompany->require();

        return DB::transaction(function () use ($invoice, $methods, $ttlMinutes, $company): array {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->company_id !== $company->id) {
                throw new LogicException('The invoice does not belong to the active company.');
            }
            if (! $this->eligibility->canCreateForInvoice($invoice->status)) {
                throw new LogicException('Only issued, overdue, or partially paid invoices can be collected.');
            }
            if ($invoice->balance_due_minor <= 0) {
                throw new LogicException('The invoice has no outstanding balance.');
            }

            $requested = $methods !== [] ? $methods : $this->methodOrder->customerDefault();
            $requested = $this->methodOrder->normalise(
                $requested,
                (bool) config('titanpay.allow_customer_stripe', false),
            );
            $availableMethods = $this->availableMethods($requested, $invoice->currency_code);
            if ($availableMethods === []) {
                throw new LogicException('No requested Titan Pay methods are currently available.');
            }

            $activeStatuses = [
                CollectionStatus::Pending->value,
                CollectionStatus::Opened->value,
                CollectionStatus::Processing->value,
                CollectionStatus::AwaitingEvidence->value,
            ];
            $reusable = CollectionSession::query()
                ->where('invoice_id', $invoice->id)
                ->where('amount_minor', $invoice->balance_due_minor)
                ->where('currency_code', strtoupper($invoice->currency_code))
                ->whereIn('status', $activeStatuses)
                ->where('expires_at', '>', now())
                ->whereNotNull('public_token_ciphertext')
                ->latest('created_at')
                ->first();

            if ($reusable && $reusable->allowed_methods_json === $availableMethods) {
                try {
                    $token = Crypt::decryptString((string) $reusable->public_token_ciphertext);
                    if (hash_equals((string) $reusable->public_token_hash, hash('sha256', $token))) {
                        $reusable->setAttribute('public_token_once', $token);

                        return ['session' => $reusable, 'token' => $token];
                    }
                } catch (\Throwable) {
                    // The active link is cancelled below so a rotated key cannot leave two live links.
                }
            }

            CollectionSession::query()
                ->where('invoice_id', $invoice->id)
                ->whereIn('status', $activeStatuses)
                ->update([
                    'status' => CollectionStatus::Cancelled->value,
                    'failure_reason' => 'Superseded by a newer secure collection link.',
                ]);

            $ttlMinutes = max(5, min($ttlMinutes, 10080));
            $token = Str::random(64);
            $session = CollectionSession::query()->create([
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'receivable_type' => 'invoice',
                'receivable_id' => $invoice->id,
                'reference' => 'TP-'.strtoupper(Str::random(12)),
                'public_token_hash' => hash('sha256', $token),
                'public_token_ciphertext' => Crypt::encryptString($token),
                'public_token_hint' => substr($token, 0, 6),
                'amount_minor' => $invoice->balance_due_minor,
                'currency_code' => strtoupper($invoice->currency_code),
                'allowed_methods_json' => $availableMethods,
                'payer_context_json' => $invoice->customer_snapshot_json,
                'status' => CollectionStatus::Pending,
                'expires_at' => now()->addMinutes($ttlMinutes),
                'created_by_user_id' => $this->actorContext->userId(),
                'created_by_agent_id' => $this->actorContext->agentId(),
                'conversation_id' => $this->actorContext->conversationId(),
                'correlation_id' => $this->actorContext->correlationId(),
            ]);
            $session->setAttribute('public_token_once', $token);

            return ['session' => $session, 'token' => $token];
        });
    }

    public function findPublic(string $token): CollectionSession
    {
        return CollectionSession::query()
            ->withoutGlobalScope('company')
            ->where('public_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    /** @return array{session:CollectionSession,initiation:GatewayInitiation} */
    public function initiate(CollectionSession $session, string $method, string $publicToken): array
    {
        return DB::transaction(function () use ($session, $method, $publicToken): array {
            $locked = CollectionSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isExpired()) {
                $locked->update(['status' => CollectionStatus::Expired]);
                throw new LogicException('Collection session has expired.');
            }
            if (! $this->eligibility->canInitiate($locked->status)) {
                throw new LogicException('This collection session can no longer be initiated.');
            }
            if (! in_array($method, $locked->allowed_methods_json ?? [], true)) {
                throw new LogicException('Payment method is not allowed.');
            }

            $existing = $this->existingInitiation($locked, $method);
            if ($existing !== null) {
                return ['session' => $locked, 'initiation' => $existing];
            }

            $connection = GatewayConnection::query()
                ->where('provider', $method)
                ->where('is_active', true)
                ->first();
            $adapter = $this->gateways->get($method);

            if (! $adapter->isAvailable($connection)) {
                throw new LogicException('The selected payment method is not configured or available.');
            }
            $this->validateConnectionLimits($locked, $connection);

            $locked->setAttribute('public_token_once', $publicToken);
            $result = $adapter->initiate($locked, $connection);
            $locked->update([
                'selected_method' => $method,
                'gateway_connection_id' => $connection?->id,
                'gateway_reference' => $result->reference,
                'gateway_payload_json' => $result->raw ?: $result->instructions,
                'status' => $result->status === 'awaiting_evidence'
                    ? CollectionStatus::AwaitingEvidence
                    : CollectionStatus::Processing,
            ]);

            return ['session' => $locked->fresh(), 'initiation' => $result];
        });
    }

    private function existingInitiation(CollectionSession $session, string $method): ?GatewayInitiation
    {
        if ($session->selected_method !== $method || ! $session->gateway_reference) {
            return null;
        }

        $payload = is_array($session->gateway_payload_json) ? $session->gateway_payload_json : [];
        $redirectUrl = is_string($payload['url'] ?? null) ? $payload['url'] : null;
        if ($redirectUrl === null) {
            foreach ($payload['links'] ?? [] as $link) {
                if (($link['rel'] ?? null) === 'approve' && is_string($link['href'] ?? null)) {
                    $redirectUrl = $link['href'];
                    break;
                }
            }
        }

        $manual = $session->status === CollectionStatus::AwaitingEvidence;

        return new GatewayInitiation(
            $manual ? 'awaiting_evidence' : 'processing',
            (string) $session->gateway_reference,
            $redirectUrl,
            $manual ? $payload : [],
            $manual ? [] : $payload,
        );
    }

    /** Capture only authorises PayPal to complete the approved order. Verified webhook processing remains payment authority. */
    public function capturePayPal(CollectionSession $session, string $orderId): array
    {
        if ($session->isExpired()) {
            $session->update(['status' => CollectionStatus::Expired]);
            throw new LogicException('Collection session has expired.');
        }
        if ($session->selected_method !== 'paypal' || $session->gateway_reference !== $orderId) {
            throw new LogicException('The PayPal order does not match this collection session.');
        }
        if (! $this->eligibility->canInitiate($session->status)) {
            throw new LogicException('This collection session can no longer be captured.');
        }

        $connection = $session->gatewayConnection()->first();
        $adapter = $this->gateways->get('paypal');
        if (! $connection || ! $adapter instanceof PayPalGatewayAdapter || ! $adapter->isAvailable($connection)) {
            throw new RuntimeException('The PayPal gateway connection is unavailable.');
        }

        $capture = $adapter->captureOrder($orderId, $connection);
        $session->update([
            'gateway_payload_json' => array_merge($session->gateway_payload_json ?? [], ['capture' => $capture]),
            'status' => CollectionStatus::Processing,
        ]);

        return $capture;
    }

    /** @return array<int,string> */
    private function availableMethods(array $methods, string $currencyCode): array
    {
        $available = [];
        foreach (array_values(array_unique($methods)) as $method) {
            if (! is_string($method) || ! in_array($method, $this->gateways->names(), true)) {
                continue;
            }

            $connection = GatewayConnection::query()
                ->where('provider', $method)
                ->where('is_active', true)
                ->first();
            $adapter = $this->gateways->get($method);

            if ($adapter->isAvailable($connection)
                && (! $connection || strtoupper($connection->currency_code) === strtoupper($currencyCode))) {
                $available[] = $method;
            }
        }

        return $available;
    }

    private function validateConnectionLimits(CollectionSession $session, ?GatewayConnection $connection): void
    {
        if (! $connection) {
            return;
        }
        if (strtoupper($connection->currency_code) !== strtoupper($session->currency_code)) {
            throw new LogicException('The selected gateway connection does not support this currency.');
        }
        if ($connection->minimum_amount_minor !== null && $session->amount_minor < $connection->minimum_amount_minor) {
            throw new LogicException('The amount is below the gateway minimum.');
        }
        if ($connection->maximum_amount_minor !== null && $session->amount_minor > $connection->maximum_amount_minor) {
            throw new LogicException('The amount exceeds the gateway maximum.');
        }
    }
}
