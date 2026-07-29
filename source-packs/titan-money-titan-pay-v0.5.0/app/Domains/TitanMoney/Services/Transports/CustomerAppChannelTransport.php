<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services\Transports;

use App\Domains\Company\Models\CompanyMembership;
use App\Domains\TitanMoney\Contracts\ChannelTransport;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Services\DeliveryReceipt;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use LogicException;

final class CustomerAppChannelTransport implements ChannelTransport
{
    public function supports(string $channel): bool { return $channel === 'customer_app'; }

    public function send(ChannelDelivery $delivery): DeliveryReceipt
    {
        $delivery->loadMissing('invoice.customer');
        $customer = $delivery->invoice?->customer;
        $customerUserId = (int) (($customer?->metadata_json ?? [])['customer_app_user_id'] ?? 0);
        if ($customerUserId < 1) {
            throw new LogicException('The customer does not have a linked Channels app account.');
        }

        $customerMembership = CompanyMembership::query()
            ->where('company_id', $delivery->company_id)
            ->where('user_id', $customerUserId)
            ->where('is_active', true)
            ->first();
        if (! $customerMembership) {
            throw new LogicException('The linked customer app account is not an active company member.');
        }

        $staffUserId = (int) CompanyMembership::query()
            ->where('company_id', $delivery->company_id)
            ->where('is_active', true)
            ->whereIn('role', ['owner','admin'])
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->value('user_id');
        if ($staffUserId < 1) {
            throw new LogicException('No company owner or administrator can own the customer channel.');
        }

        [$conversation, $message] = DB::transaction(function () use ($delivery, $customerUserId, $staffUserId, $customer): array {
            $conversation = Conversation::query()
                ->where('company_id', $delivery->company_id)
                ->where('type', 'customer_channel')
                ->whereHas('participants', fn ($query) => $query->where('user_id', $customerUserId))
                ->first();

            if (! $conversation) {
                $conversation = Conversation::query()->create([
                    'company_id' => $delivery->company_id,
                    'created_by_user_id' => $staffUserId,
                    'name' => ($customer?->name ?: 'Customer').' — Billing',
                    'type' => 'customer_channel',
                ]);
            }

            foreach (array_unique([$customerUserId, $staffUserId]) as $userId) {
                Participant::query()->firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                ]);
            }

            $payload = $delivery->payload_json ?? [];
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'message' => trim((string) ($payload['body'] ?? 'Invoice update')).($payload['payment_url'] ?? null ? "\n\nPay securely: ".$payload['payment_url'] : ''),
                'metadata_json' => [
                    'message_type' => 'invoice_delivery',
                    'delivery_id' => $delivery->id,
                    'invoice_id' => $delivery->invoice_id,
                    'invoice_number' => $payload['invoice_number'] ?? null,
                    'payment_url' => $payload['payment_url'] ?? null,
                    'qr_url' => $payload['qr_url'] ?? null,
                    'payment_methods' => $payload['payment_methods'] ?? [],
                ],
            ]);
            $conversation->touch();

            return [$conversation, $message];
        });

        broadcast(new MessageSent($message))->toOthers();

        return new DeliveryReceipt('titan-channels', (string) $message->id, 'sent', [
            'conversation_id'=>$conversation->id,
            'message_id'=>$message->id,
        ], true);
    }
}
