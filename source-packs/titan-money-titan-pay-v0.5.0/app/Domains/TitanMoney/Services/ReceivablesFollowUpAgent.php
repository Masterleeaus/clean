<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\AI\FollowUpStageResolver;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\InvoiceFollowUp;
use Carbon\CarbonImmutable;
use Throwable;

final class ReceivablesFollowUpAgent
{
    public const AGENT_ID = 'titan-money.receivables-agent';

    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly AgentRunService $runs,
        private readonly AgentApprovalService $approvals,
        private readonly InvoiceLifecycleService $lifecycle,
        private readonly FollowUpStageResolver $resolver,
        private readonly ChannelDeliveryService $deliveries,
        private readonly TitanMoneyOutbox $outbox,
    ) {}

    public function run(AutomationPolicy $policy, bool $dryRun = false, string $triggerType = 'schedule'): AgentRun
    {
        $company = $policy->company()->withoutGlobalScope('company')->firstOrFail();
        $this->currentCompany->set($company);
        $run = $this->runs->start($policy, self::AGENT_ID, $triggerType, ['policy_id'=>$policy->id], $dryRun);
        $examined = $acted = $escalated = 0;
        $results = [];
        $decisions = [];

        try {
            $settings = $policy->settings_json ?? [];
            $stages = is_array($settings['stages'] ?? null) ? $settings['stages'] : [];
            $limit = max(1, (int) ($settings['max_invoices_per_run'] ?? 200));
            $invoices = Invoice::query()
                ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value])
                ->where('balance_due_minor', '>', 0)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', now()->toDateString())
                ->with(['customer'])
                ->orderBy('due_date')
                ->limit($limit)
                ->get();

            foreach ($invoices as $invoice) {
                $examined++;
                $daysOverdue = (int) CarbonImmutable::parse($invoice->due_date)->startOfDay()->diffInDays(CarbonImmutable::now()->startOfDay(), false);
                $completed = InvoiceFollowUp::query()->where('invoice_id',$invoice->id)->whereIn('status',['queued','sent','handed_off','approval_pending'])->pluck('stage_key')->all();
                $stageKey = $this->resolver->resolve($daysOverdue, $stages, $completed);
                $decisions[$invoice->id] = $stageKey ?: 'none';

                if ($stageKey === null) {
                    continue;
                }

                $stage = $this->resolver->stage($stageKey, $stages) ?? [];
                if ($dryRun || $policy->autonomy_level === 'observe') {
                    $results[$invoice->id] = ['stage_key'=>$stageKey,'decision'=>'observe','days_overdue'=>$daysOverdue];
                    continue;
                }

                if ($daysOverdue > 0) {
                    $invoice = $this->lifecycle->markOverdue($invoice);
                }
                $followUp = InvoiceFollowUp::query()->firstOrCreate([
                    'invoice_id'=>$invoice->id,
                    'stage_key'=>$stageKey,
                ], [
                    'company_id'=>$company->id,
                    'agent_run_id'=>$run->id,
                    'days_overdue'=>$daysOverdue,
                    'status'=>'planned',
                    'tone'=>(string)($stage['tone'] ?? 'friendly'),
                    'channels_json'=>array_values($stage['channels'] ?? $policy->channels_json ?? ['email']),
                    'scheduled_at'=>now(),
                    'metadata_json'=>['template'=>$stage['template'] ?? 'invoice_reminder','escalate'=>(bool)($stage['escalate'] ?? false)],
                ]);

                if (! $followUp->wasRecentlyCreated) {
                    continue;
                }

                if ((bool)($stage['approval_required'] ?? false) || $policy->autonomy_level === 'draft') {
                    $this->approvals->request(
                        $run,
                        'send_invoice_follow_up',
                        $followUp,
                        ['follow_up_id'=>$followUp->id,'invoice_id'=>$invoice->id,'stage_key'=>$stageKey],
                        'This follow-up stage requires human approval under the current receivables policy.',
                    );
                    $followUp->update(['status'=>'approval_pending']);
                    $escalated++;
                    $results[$invoice->id] = ['follow_up_id'=>$followUp->id,'decision'=>'approval'];
                    continue;
                }

                $channels = array_values($stage['channels'] ?? $policy->channels_json ?? ['email']);
                $queued = $this->deliveries->queueFollowUp($followUp, $channels, (string)($stage['template'] ?? 'invoice_reminder'));
                $this->outbox->record('invoice.follow_up_queued', $invoice, [
                    'follow_up_id'=>$followUp->id,
                    'stage_key'=>$stageKey,
                    'days_overdue'=>$daysOverdue,
                    'channels'=>$channels,
                    'deliveries_queued'=>$queued,
                ]);
                $acted++;
                if ((bool)($stage['escalate'] ?? false)) {
                    $escalated++;
                }
                $results[$invoice->id] = ['follow_up_id'=>$followUp->id,'decision'=>'queued','deliveries'=>$queued];
            }

            return $this->runs->complete($run, $examined, $acted, $escalated, $results, $decisions);
        } catch (Throwable $error) {
            return $this->runs->fail($run, $error, $examined, $acted, $escalated);
        }
    }
}
