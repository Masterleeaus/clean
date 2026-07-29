<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Events\TitanChannelsDeliveryRequested;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Models\InvoiceReminder;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ChannelDeliveryDispatcher
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly ChannelTransportRegistry $transports,
    ) {}

    public function dispatchDue(int $limit = 100): int
    {
        ChannelDelivery::query()->withoutGlobalScope('company')
            ->where('status','processing')
            ->where('updated_at','<',now()->subMinutes(15))
            ->update(['status'=>'queued','scheduled_at'=>now()]);

        $count = 0;
        $ids = ChannelDelivery::query()->withoutGlobalScope('company')
            ->where('status','queued')
            ->where('scheduled_at','<=',now())
            ->orderBy('scheduled_at')
            ->limit(max(1, $limit))
            ->pluck('id');

        foreach ($ids as $id) {
            $claimed = ChannelDelivery::query()->withoutGlobalScope('company')
                ->whereKey($id)
                ->where('status','queued')
                ->where('scheduled_at','<=',now())
                ->update([
                    'status'=>'processing',
                    'attempts'=>DB::raw('attempts + 1'),
                    'updated_at'=>now(),
                ]);
            if ($claimed !== 1) continue;

            $delivery = ChannelDelivery::query()->withoutGlobalScope('company')->findOrFail($id);
            $this->currentCompany->set($delivery->company()->withoutGlobalScope('company')->firstOrFail());
            $this->actorContext->setAgent('titan-money.delivery-agent', (string) $delivery->id);
            $attempts = (int) $delivery->attempts;

            try {
                $receipt = $this->transports->for($delivery->channel)->send($delivery);
                $delivery->update([
                    'status'=>'sent',
                    'provider'=>$receipt->provider,
                    'external_message_id'=>$receipt->externalMessageId,
                    'receipt_json'=>$receipt->raw,
                    'sent_at'=>now(),
                    'delivered_at'=>$receipt->delivered ? now() : null,
                    'failed_at'=>null,
                    'last_error'=>null,
                ]);
                $delivery->followUp?->update(['status'=>'sent','sent_at'=>now()]);
                $this->markReminderComplete($delivery, $receipt->delivered ? 'delivered' : $receipt->status);
                event(new TitanChannelsDeliveryRequested($delivery->fresh()));
                $count++;
            } catch (Throwable $error) {
                $failed = $attempts >= 5;
                $delivery->update([
                    'status'=>$failed ? 'failed' : 'queued',
                    'scheduled_at'=>now()->addMinutes(min(60, 2 ** min($attempts, 5))),
                    'failed_at'=>$failed ? now() : null,
                    'last_error'=>mb_substr($error->getMessage(),0,4000),
                ]);
                if ($failed) $this->markReminderFailed($delivery, $error->getMessage());
            }
        }

        return $count;
    }

    private function markReminderComplete(ChannelDelivery $delivery, string $deliveryStatus): void
    {
        $reminderId = ($delivery->payload_json ?? [])['reminder_id'] ?? null;
        if (! $reminderId) return;
        $reminder = InvoiceReminder::query()->whereKey($reminderId)->first();
        if ($reminder) {
            $reminder->update([
                'status'=>'sent',
                'sent_at'=>now(),
                'metadata_json'=>array_merge($reminder->metadata_json ?? [], ['delivery_status'=>$deliveryStatus,'delivery_id'=>$delivery->id]),
            ]);
        }
    }

    private function markReminderFailed(ChannelDelivery $delivery, string $error): void
    {
        $reminderId = ($delivery->payload_json ?? [])['reminder_id'] ?? null;
        if ($reminderId) {
            InvoiceReminder::query()->whereKey($reminderId)->update(['status'=>'failed','error_message'=>mb_substr($error,0,4000)]);
        }
    }
}
