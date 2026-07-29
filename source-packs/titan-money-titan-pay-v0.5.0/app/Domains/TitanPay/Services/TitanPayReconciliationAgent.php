<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Services\AgentRunService;
use App\Domains\TitanPay\Models\BankDeposit;
use App\Domains\TitanPay\Models\GatewayEvent;
use Throwable;

final class TitanPayReconciliationAgent
{
    public const AGENT_ID = 'titan-pay.reconciliation-agent';

    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly AgentRunService $runs,
    ) {}

    public function run(AutomationPolicy $policy, bool $dryRun = false, string $triggerType = 'schedule'): AgentRun
    {
        $company = $policy->company()->withoutGlobalScope('company')->firstOrFail();
        $this->currentCompany->set($company);
        $run = $this->runs->start($policy, self::AGENT_ID, $triggerType, ['policy_id'=>$policy->id], $dryRun);
        $examined = $acted = $escalated = 0;
        $results = [];

        try {
            $settings = $policy->settings_json ?? [];
            $limit = max(1, (int)($settings['max_events_per_run'] ?? 100));
            $gatewayEvents = GatewayEvent::query()->whereIn('processing_status',['unmatched','rejected','processed_unallocated'])->latest('received_at')->limit($limit)->get();
            $deposits = BankDeposit::query()->where('status','unmatched')->latest('transaction_date')->limit($limit)->get();

            foreach ($gatewayEvents as $event) {
                $examined++;
                $results['gateway_event:'.$event->id] = ['status'=>$event->processing_status,'reason'=>$event->failure_reason];
                if (! $dryRun && $policy->autonomy_level !== 'observe') {
                    $created = $this->queueException('gateway_event', $event->id, [
                        'provider'=>$event->provider,
                        'event_type'=>$event->event_type,
                        'processing_status'=>$event->processing_status,
                        'failure_reason'=>$event->failure_reason,
                    ]);
                    $acted += $created ? 1 : 0;
                    $escalated += $created ? 1 : 0;
                }
            }

            foreach ($deposits as $deposit) {
                $examined++;
                $results['bank_deposit:'.$deposit->id] = ['amount_minor'=>$deposit->amount_minor,'reference'=>$deposit->reference];
                if (! $dryRun && $policy->autonomy_level !== 'observe') {
                    $created = $this->queueException('bank_deposit', $deposit->id, [
                        'amount_minor'=>$deposit->amount_minor,
                        'currency_code'=>$deposit->currency_code,
                        'reference'=>$deposit->reference,
                        'transaction_date'=>$deposit->transaction_date?->toDateString(),
                    ]);
                    $acted += $created ? 1 : 0;
                    $escalated += $created ? 1 : 0;
                }
            }

            return $this->runs->complete($run, $examined, $acted, $escalated, $results);
        } catch (Throwable $error) {
            return $this->runs->fail($run, $error, $examined, $acted, $escalated);
        }
    }

    private function queueException(string $type, string $id, array $payload): bool
    {
        $delivery = ChannelDelivery::query()->firstOrCreate([
            'idempotency_key'=>'titanpay-reconciliation:'.$type.':'.$id,
        ], [
            'company_id'=>$this->currentCompany->require()->id,
            'channel'=>'internal',
            'recipient_type'=>'company_finance_team',
            'recipient_id'=>$this->currentCompany->require()->id,
            'template_key'=>'payment_reconciliation_exception',
            'status'=>'queued',
            'payload_json'=>array_merge(['exception_type'=>$type,'exception_id'=>$id],$payload),
            'scheduled_at'=>now(),
        ]);

        return $delivery->wasRecentlyCreated;
    }
}
