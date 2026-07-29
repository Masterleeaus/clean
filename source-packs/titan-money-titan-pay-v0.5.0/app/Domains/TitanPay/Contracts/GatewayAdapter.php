<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Contracts;

use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\ValueObjects\GatewayInitiation;
use App\Domains\TitanPay\ValueObjects\WebhookVerification;
use Illuminate\Http\Request;

interface GatewayAdapter
{
    public function name(): string;
    public function isAvailable(?GatewayConnection $connection = null): bool;
    public function initiate(CollectionSession $session, ?GatewayConnection $connection = null): GatewayInitiation;
    public function verifyWebhook(Request $request, ?GatewayConnection $connection = null): WebhookVerification;
    public function refund(string $transactionReference, int $amountMinor, string $currencyCode, ?GatewayConnection $connection = null): array;
}
