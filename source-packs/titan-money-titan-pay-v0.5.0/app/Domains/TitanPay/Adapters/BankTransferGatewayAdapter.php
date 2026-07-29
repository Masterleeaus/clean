<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Adapters;

use App\Domains\TitanPay\Contracts\SecretResolver;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use Throwable;

final class BankTransferGatewayAdapter extends AbstractManualAdapter
{
    public function __construct(private readonly SecretResolver $secrets) {}

    public function name(): string
    {
        return 'bank_transfer';
    }

    public function isAvailable(?GatewayConnection $connection = null): bool
    {
        if (! $connection?->vault_secret_reference) {
            return false;
        }

        try {
            $details = $this->details($connection);
        } catch (Throwable) {
            return false;
        }

        return is_string($details['bsb'] ?? null) && trim((string) $details['bsb']) !== ''
            && is_string($details['account_number'] ?? null) && trim((string) $details['account_number']) !== '';
    }

    protected function instructions(CollectionSession $session, ?GatewayConnection $connection): array
    {
        $details = $connection ? $this->details($connection) : [];

        return [
            'message' => 'Transfer funds using the exact reference. A receipt or verified bank match is required.',
            'bsb' => $details['bsb'] ?? null,
            'account_number' => $details['account_number'] ?? null,
            'account_name' => $details['account_name'] ?? null,
            'reference' => $session->reference,
            'amount_minor' => $session->amount_minor,
            'currency_code' => $session->currency_code,
        ];
    }

    /** @return array<string,mixed> */
    private function details(GatewayConnection $connection): array
    {
        return $this->secrets->resolve((string) $connection->vault_secret_reference);
    }
}
