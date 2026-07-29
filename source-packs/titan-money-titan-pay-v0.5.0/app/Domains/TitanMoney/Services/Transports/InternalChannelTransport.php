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

final class InternalChannelTransport implements ChannelTransport
{
    public function supports(string $channel): bool { return $channel === 'internal'; }

    public function send(ChannelDelivery $delivery): DeliveryReceipt
    {
        $staff = CompanyMembership::query()->where('company_id',$delivery->company_id)->where('is_active',true)
            ->whereIn('role',['owner','admin'])->pluck('user_id')->map(fn ($id)=>(int)$id)->all();
        if ($staff === []) throw new LogicException('No staff recipients are available for the internal alert.');

        [$conversation,$message] = DB::transaction(function () use ($delivery,$staff): array {
            $conversation = Conversation::query()->firstOrCreate([
                'company_id'=>$delivery->company_id,
                'type'=>'system_channel',
                'name'=>'Titan Money Alerts',
            ], ['created_by_user_id'=>$staff[0]]);
            foreach ($staff as $userId) Participant::query()->firstOrCreate(['conversation_id'=>$conversation->id,'user_id'=>$userId]);
            $message = Message::query()->create([
                'conversation_id'=>$conversation->id,
                'user_id'=>null,
                'message'=>trim(
                    (($delivery->payload_json['delivery_exception'] ?? null)
                        ? "Delivery exception: ".$delivery->payload_json['delivery_exception']."\n\n"
                        : '')
                    .(string)($delivery->payload_json['body'] ?? 'Titan Money alert')
                ),
                'metadata_json'=>['message_type'=>'internal_finance_alert','delivery_id'=>$delivery->id,'invoice_id'=>$delivery->invoice_id],
            ]);
            $conversation->touch();
            return [$conversation,$message];
        });
        broadcast(new MessageSent($message))->toOthers();

        return new DeliveryReceipt('titan-channels',(string)$message->id,'sent',['conversation_id'=>$conversation->id],true);
    }
}
