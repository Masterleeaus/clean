<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\InvoiceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class InvoiceReminderService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly TitanMoneyOutbox $outbox,
    ) {}

    /** @param array<int,int> $offsets */
    public function schedule(Invoice $invoice, array $offsets = [-7, 0, 7, 14], string $channel = 'email'): void
    {
        $invoice->reminders()->where('status', 'pending')->delete();
        if (! $invoice->due_date) {
            return;
        }

        foreach (array_unique(array_map('intval', $offsets)) as $offset) {
            $scheduled = CarbonImmutable::parse($invoice->due_date)->addDays($offset)->setTime(9, 0);
            if ($scheduled->isPast()) {
                continue;
            }
            $invoice->reminders()->create([
                'days_offset'=>$offset,
                'type'=>$offset < 0 ? 'before_due' : ($offset === 0 ? 'on_due' : 'after_due'),
                'channel'=>$channel,
                'scheduled_at'=>$scheduled,
                'status'=>'pending',
            ]);
        }
    }

    public function enqueueDue(): int
    {
        $count = 0;
        $ids = InvoiceReminder::query()
            ->withoutGlobalScope('company')
            ->where('status','pending')
            ->where('scheduled_at','<=',now())
            ->orderBy('id')
            ->limit(1000)
            ->pluck('id');

        foreach ($ids as $id) {
            $queued = DB::transaction(function () use ($id): bool {
                $reminder = InvoiceReminder::query()
                    ->withoutGlobalScope('company')
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if (! $reminder || $reminder->status !== 'pending' || $reminder->scheduled_at?->isFuture()) {
                    return false;
                }

                $invoice = Invoice::query()->withoutGlobalScope('company')->find($reminder->invoice_id);
                if (! $invoice || in_array($invoice->status->value, ['paid','voided','written_off'], true)) {
                    $reminder->update(['status'=>'cancelled']);
                    return false;
                }

                $company = $invoice->company()->withoutGlobalScope('company')->firstOrFail();
                $this->currentCompany->set($company);
                $this->actorContext->setAgent('titan-money.reminder-agent', 'reminder:'.$reminder->id);
                $this->outbox->record('invoice.reminder_due', $invoice, [
                    'reminder_id'=>$reminder->id,
                    'type'=>$reminder->type,
                    'channel'=>$reminder->channel,
                ]);
                $reminder->update(['status'=>'queued']);

                return true;
            });

            if ($queued) {
                $count++;
            }
        }

        return $count;
    }
}
