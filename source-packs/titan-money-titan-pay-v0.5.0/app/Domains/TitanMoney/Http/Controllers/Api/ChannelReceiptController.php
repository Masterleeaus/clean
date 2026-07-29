<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers\Api;

use App\Domains\TitanMoney\Models\ChannelDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use JsonException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ChannelReceiptController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = trim((string) config('titan_channels.receipt_secret', ''));
        if ($secret === '') {
            throw new HttpException(503, 'Delivery receipts are not configured.');
        }

        $raw = $request->getContent();
        $timestamp = trim((string) $request->header('X-Titan-Channels-Timestamp', ''));
        $signature = trim((string) $request->header('X-Titan-Channels-Signature', ''));
        $signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $tolerance = max(30, (int) config('titan_channels.receipt_tolerance_seconds', 300));

        if (! ctype_digit($timestamp) || abs(now()->timestamp - (int) $timestamp) > $tolerance) {
            throw new HttpException(401, 'Receipt timestamp is invalid.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$raw, $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            throw new HttpException(401, 'Receipt signature is invalid.');
        }

        try {
            $payload = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpException(422, 'Receipt payload is invalid.');
        }

        if (! is_array($payload)) {
            throw new HttpException(422, 'Receipt payload is invalid.');
        }

        $deliveryId = is_string($payload['delivery_id'] ?? null) ? trim($payload['delivery_id']) : '';
        $status = is_string($payload['status'] ?? null) ? strtolower(trim($payload['status'])) : '';
        $externalId = is_scalar($payload['external_message_id'] ?? null) ? trim((string) $payload['external_message_id']) : null;
        $receiptId = is_scalar($payload['receipt_id'] ?? null) ? trim((string) $payload['receipt_id']) : null;
        $allowed = ['accepted','queued','sent','delivered','completed','failed','bounced','undeliverable','rejected'];

        if ($deliveryId === '' || ! in_array($status, $allowed, true)) {
            throw new HttpException(422, 'Receipt fields are invalid.');
        }

        $delivery = DB::transaction(function () use ($deliveryId, $status, $externalId, $receiptId, $payload): ChannelDelivery {
            $delivery = ChannelDelivery::query()
                ->withoutGlobalScope('company')
                ->whereKey($deliveryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($externalId !== null && $delivery->external_message_id !== null
                && ! hash_equals((string) $delivery->external_message_id, $externalId)) {
                throw new HttpException(409, 'Receipt message identifier does not match.');
            }

            $receipt = is_array($delivery->receipt_json) ? $delivery->receipt_json : [];
            $seen = array_values(array_filter((array) ($receipt['receipt_ids'] ?? []), 'is_string'));
            if ($receiptId !== null && $receiptId !== '' && in_array($receiptId, $seen, true)) {
                return $delivery;
            }
            if ($receiptId !== null && $receiptId !== '') {
                $seen[] = $receiptId;
            }

            $receipt['receipt_ids'] = array_slice(array_values(array_unique($seen)), -100);
            $receipt['latest'] = $payload;
            $receipt['latest_received_at'] = now()->toIso8601String();

            $updates = [
                'external_message_id' => $externalId ?: $delivery->external_message_id,
                'receipt_json' => $receipt,
            ];
            $isDeliveredReceipt = in_array($status, ['delivered','completed'], true);

            if ($delivery->delivered_at !== null && ! $isDeliveredReceipt) {
                $delivery->update($updates);

                return $delivery->fresh();
            }
            if ($delivery->failed_at !== null && ! $isDeliveredReceipt) {
                $delivery->update($updates);

                return $delivery->fresh();
            }

            if ($isDeliveredReceipt) {
                $updates += [
                    'status' => 'sent',
                    'sent_at' => $delivery->sent_at ?: now(),
                    'delivered_at' => $delivery->delivered_at ?: now(),
                    'failed_at' => null,
                    'last_error' => null,
                ];
            } elseif (in_array($status, ['failed','bounced','undeliverable','rejected'], true)) {
                $updates += [
                    'status' => 'failed',
                    'failed_at' => now(),
                    'last_error' => mb_substr((string) ($payload['error'] ?? 'The channel provider reported delivery failure.'), 0, 4000),
                ];
            } else {
                $updates += [
                    'status' => 'sent',
                    'sent_at' => $delivery->sent_at ?: now(),
                    'failed_at' => null,
                    'last_error' => null,
                ];
            }

            $delivery->update($updates);

            return $delivery->fresh();
        });

        return response()->json([
            'accepted' => true,
            'delivery_id' => $delivery->id,
            'status' => $delivery->status,
        ], 202);
    }
}
