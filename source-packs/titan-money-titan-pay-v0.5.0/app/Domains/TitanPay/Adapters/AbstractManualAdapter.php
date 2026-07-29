<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Adapters;

use App\Domains\TitanPay\Contracts\GatewayAdapter;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\ValueObjects\GatewayInitiation;
use App\Domains\TitanPay\ValueObjects\WebhookVerification;
use Illuminate\Http\Request;

abstract class AbstractManualAdapter implements GatewayAdapter
{
    public function isAvailable(?GatewayConnection $connection = null): bool { return true; }
    public function initiate(CollectionSession $session, ?GatewayConnection $connection = null): GatewayInitiation
    {
        return new GatewayInitiation('awaiting_evidence', $session->reference, null, $this->instructions($session, $connection));
    }
    public function verifyWebhook(Request $request, ?GatewayConnection $connection = null): WebhookVerification
    {
        return new WebhookVerification(false, 'manual-'.hash('sha256',$request->getContent()), 'manual', failureReason: 'Manual payment methods do not accept authoritative webhooks.');
    }
    public function refund(string $transactionReference, int $amountMinor, string $currencyCode, ?GatewayConnection $connection = null): array
    {
        return ['status'=>'manual_review_required','reference'=>$transactionReference,'amount_minor'=>$amountMinor,'currency_code'=>$currencyCode];
    }
    abstract protected function instructions(CollectionSession $session, ?GatewayConnection $connection): array;
}
