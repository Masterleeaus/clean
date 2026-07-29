<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Support;

use App\Domains\TitanMoney\Support\DecimalParser;

final class PayPalWebhookParser
{
    /** @return array{event_id:string,event_type:string,session_reference:string,transaction_id:string,amount_minor:?int,currency_code:?string,status:string} */
    public function parse(array $data): array
    {
        $resource = is_array($data['resource'] ?? null) ? $data['resource'] : [];
        $purchase = is_array($resource['purchase_units'][0] ?? null) ? $resource['purchase_units'][0] : [];
        $relatedIds = is_array($resource['supplementary_data']['related_ids'] ?? null)
            ? $resource['supplementary_data']['related_ids']
            : [];
        $amount = is_array($resource['amount'] ?? null)
            ? $resource['amount']
            : (is_array($purchase['amount'] ?? null) ? $purchase['amount'] : []);
        $eventType = (string) ($data['event_type'] ?? 'unknown');

        $sessionReference = (string) (
            $relatedIds['order_id']
            ?? $purchase['reference_id']
            ?? $resource['invoice_id']
            ?? ''
        );

        return [
            'event_id' => (string) ($data['id'] ?? hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES) ?: '')),
            'event_type' => $eventType,
            'session_reference' => $sessionReference,
            'transaction_id' => (string) ($resource['id'] ?? $relatedIds['capture_id'] ?? ''),
            'amount_minor' => isset($amount['value']) ? DecimalParser::minorUnits((string) $amount['value']) : null,
            'currency_code' => isset($amount['currency_code']) ? strtoupper((string) $amount['currency_code']) : null,
            'status' => str_contains($eventType, 'COMPLETED') ? 'completed' : 'ignored',
        ];
    }
}
