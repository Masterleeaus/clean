<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\BillableEvent;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\RecurringInvoice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class RecurringInvoiceService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly BillableEventService $billableEvents,
    ) {}

    public function generateDue(?CarbonImmutable $at = null): int
    {
        $at ??= CarbonImmutable::now();
        $count = 0;

        RecurringInvoice::query()->withoutGlobalScope('company')
            ->where('status', 'active')
            ->whereDate('next_generation_date', '<=', $at->toDateString())
            ->orderBy('next_generation_date')
            ->chunkById(100, function ($records) use (&$count, $at): void {
                foreach ($records as $recurring) {
                    $company = $recurring->company()->withoutGlobalScope('company')->firstOrFail();
                    $this->currentCompany->set($company);
                    $this->actorContext->setAgent('titan-money.recurring-invoice-agent', 'recurring:'.$recurring->id);

                    DB::transaction(function () use ($recurring, $at, &$count): void {
                        $locked = RecurringInvoice::query()->whereKey($recurring->id)->lockForUpdate()->firstOrFail();
                        if ($locked->stop_after !== null && $locked->occurrences >= $locked->stop_after) {
                            $locked->update(['status'=>'completed']);
                            return;
                        }

                        $generationDate = $locked->next_generation_date->format('Y-m-d');
                        $idempotency = 'recurring:'.$locked->id.':'.$generationDate;
                        $alreadyExists = BillableEvent::query()->where('idempotency_key', $idempotency)->exists()
                            || Invoice::query()->where('source_idempotency_key', $idempotency)->exists();

                        if (! $alreadyExists) {
                            $termsDays = max(0, (int) config('titanmoney.default_payment_terms_days', 14));
                            $this->billableEvents->ingest([
                                'source_type'=>'recurring_obligation',
                                'source_id'=>(string) $locked->id,
                                'source_version'=>(string) $locked->occurrences,
                                'idempotency_key'=>$idempotency,
                                'customer_id'=>(string) $locked->customer_id,
                                'occurred_at'=>$at,
                                'available_at'=>$at,
                                'payload'=>[
                                    'currency_code'=>$locked->currency_code,
                                    'tax_inclusive'=>$locked->tax_inclusive,
                                    'issue_date'=>$at->toDateString(),
                                    'due_date'=>$at->addDays($termsDays)->toDateString(),
                                    'payment_terms_code'=>$locked->payment_terms_code ?: 'NET'.$termsDays,
                                    'service_agreement_id'=>$locked->service_agreement_id,
                                    'notes'=>$locked->notes,
                                    'lines'=>$locked->lines->map(static fn ($line): array => [
                                        'line_type'=>$line->line_type,
                                        'description'=>$line->description,
                                        'quantity'=>(string) $line->quantity,
                                        'unit_price_minor'=>(int) $line->unit_price_minor,
                                        'discount_minor'=>(int) $line->discount_minor,
                                        'tax_rate_basis_points'=>(int) $line->tax_rate_basis_points,
                                        'product_id'=>$line->product_id,
                                    ])->all(),
                                ],
                            ]);
                            $count++;
                        }

                        $locked->occurrences++;
                        $locked->last_generated_at = $at;
                        $locked->next_generation_date = $this->nextDate($locked->next_generation_date, $locked->frequency, $locked->interval_count);
                        if (($locked->end_date && $locked->next_generation_date->gt($locked->end_date)) || ($locked->stop_after !== null && $locked->occurrences >= $locked->stop_after)) {
                            $locked->status = 'completed';
                        }
                        $locked->save();
                    });
                }
            });

        return $count;
    }

    public function nextDate(CarbonImmutable|\Carbon\Carbon $date, string $frequency, int $interval = 1): CarbonImmutable
    {
        $date = CarbonImmutable::instance($date);

        return match ($frequency) {
            'daily' => $date->addDays($interval),
            'weekly' => $date->addWeeks($interval),
            'fortnightly' => $date->addWeeks(2 * $interval),
            'monthly' => $date->addMonthsNoOverflow($interval),
            'quarterly' => $date->addMonthsNoOverflow(3 * $interval),
            'semiannual', 'six_monthly' => $date->addMonthsNoOverflow(6 * $interval),
            'annual' => $date->addYearsNoOverflow($interval),
            default => $date->addMonthsNoOverflow($interval),
        };
    }
}
