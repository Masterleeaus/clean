<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\AI\AgentAuthority;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Models\BillableEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class InvoiceAutomationAgent
{
    public const AGENT_ID = 'titan-money.invoice-agent';

    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly AutomationPolicyService $policies,
        private readonly AgentAuthority $authority,
        private readonly AgentRunService $runs,
        private readonly AgentApprovalService $approvals,
        private readonly InvoiceCalculator $calculator,
        private readonly InvoiceService $invoices,
        private readonly InvoiceDocumentService $documents,
        private readonly InvoiceReminderService $reminders,
    ) {}

    public function run(AutomationPolicy $policy, bool $dryRun = false, string $triggerType = 'schedule'): AgentRun
    {
        $company = $policy->company()->withoutGlobalScope('company')->firstOrFail();
        $this->currentCompany->set($company);
        $run = $this->runs->start($policy, self::AGENT_ID, $triggerType, ['policy_id'=>$policy->id], $dryRun);
        $examined = $acted = $escalated = 0;
        $decisions = [];
        $results = [];

        try {
            $settings = $policy->settings_json ?? [];
            $events = BillableEvent::query()->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('status', 'pending')
                ->where('available_at', '<=', now())
                ->orderBy('available_at')
                ->limit(max(1, (int) ($settings['max_events_per_run'] ?? 100)))
                ->get();

            foreach ($events as $event) {
                $examined++;
                $result = $this->processEvent($policy, $event, $run, $dryRun);
                $decisions[$event->id] = $result['decision'];
                $results[$event->id] = $result;
                $acted += (int) ($result['acted'] ?? 0);
                $escalated += (int) ($result['escalated'] ?? 0);
            }

            return $this->runs->complete($run, $examined, $acted, $escalated, $results, $decisions);
        } catch (Throwable $error) {
            return $this->runs->fail($run, $error, $examined, $acted, $escalated);
        }
    }

    /** Process exactly one billable event for synchronous chat and event-driven workflows. */
    public function runForEvent(AutomationPolicy $policy, BillableEvent $event, bool $dryRun = false, string $triggerType = 'chat'): AgentRun
    {
        $company = $policy->company()->withoutGlobalScope('company')->firstOrFail();
        if ($event->company_id !== $company->id) {
            throw new \LogicException('The billable event and automation policy belong to different companies.');
        }

        $this->currentCompany->set($company);
        $run = $this->runs->start(
            policy: $policy,
            agentId: self::AGENT_ID,
            triggerType: $triggerType,
            trigger: [
                'policy_id' => $policy->id,
                'billable_event_id' => $event->id,
            ],
            dryRun: $dryRun,
            correlationId: $this->actorContext->correlationId(),
        );

        try {
            $result = $this->processEvent($policy, $event, $run, $dryRun);

            return $this->runs->complete(
                $run,
                1,
                (int) ($result['acted'] ?? 0),
                (int) ($result['escalated'] ?? 0),
                [$event->id => $result],
                [$event->id => $result['decision']],
            );
        } catch (Throwable $error) {
            return $this->runs->fail($run, $error, 1, 0, 0);
        }
    }

    /** @return array<string,mixed> */
    private function processEvent(AutomationPolicy $policy, BillableEvent $event, AgentRun $run, bool $dryRun): array
    {
        $payload = $event->payload_json ?? [];
        $lines = is_array($payload['lines'] ?? null) ? $payload['lines'] : [];
        $calculated = $this->calculator->calculate($lines, (bool) ($payload['tax_inclusive'] ?? false));
        $decision = $this->authority->invoiceDecision(
            $this->policies->authorityPayload($policy),
            (int) $calculated['total_minor'],
            $event->source_type,
        );

        if ($dryRun || $decision === 'observe') {
            return ['decision'=>$decision,'amount_minor'=>$calculated['total_minor'],'acted'=>0,'escalated'=>0];
        }
        if ($decision === 'skip') {
            return ['decision'=>'skip','reason'=>'automation_policy_disabled','acted'=>0,'escalated'=>0];
        }

        try {
            return DB::transaction(function () use ($event, $payload, $lines, $policy, $decision, $run): array {
                $locked = BillableEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
                if ($locked->status === 'processed' && $locked->invoice_id) {
                    return [
                        'decision'=>'replay',
                        'invoice_id'=>$locked->invoice_id,
                        'status'=>$locked->invoice?->status?->value,
                        'acted'=>0,
                        'escalated'=>0,
                    ];
                }
                if ($locked->status !== 'pending') {
                    return ['decision'=>'skip','reason'=>'event_not_pending','acted'=>0,'escalated'=>0];
                }

                $settings = $policy->settings_json ?? [];
                $termsDays = max(0, (int) ($settings['payment_terms_days'] ?? config('titanmoney.default_payment_terms_days', 14)));
                $issueDate = CarbonImmutable::parse($payload['issue_date'] ?? now()->toDateString());
                $invoice = $this->invoices->createDraft([
                    'customer_id' => $locked->customer_id,
                    'source_type' => $locked->source_type,
                    'source_id' => $locked->source_id,
                    'source_version' => $locked->source_version,
                    'source_idempotency_key' => $locked->idempotency_key,
                    'currency_code' => strtoupper((string) ($payload['currency_code'] ?? config('titanmoney.default_currency', 'AUD'))),
                    'tax_inclusive' => (bool) ($payload['tax_inclusive'] ?? false),
                    'issue_date' => $issueDate->toDateString(),
                    'due_date' => (string) ($payload['due_date'] ?? $issueDate->addDays($termsDays)->toDateString()),
                    'payment_terms_code' => (string) ($payload['payment_terms_code'] ?? 'NET'.$termsDays),
                    'property_id' => $payload['property_id'] ?? null,
                    'job_id' => $payload['job_id'] ?? null,
                    'work_order_id' => $payload['work_order_id'] ?? null,
                    'service_agreement_id' => $payload['service_agreement_id'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'customer_message' => $payload['customer_message'] ?? null,
                ], $lines);

                $escalated = 0;
                if ($decision === 'issue') {
                    $invoice = $this->invoices->issue($invoice);
                    $this->documents->snapshot($invoice);
                    $this->reminders->schedule($invoice, config('titanmoney.reminder_offsets', [-7,0,7,14]));
                } elseif ($decision === 'approval') {
                    $this->approvals->request(
                        $run,
                        'issue_invoice',
                        $invoice,
                        ['invoice_id'=>$invoice->id,'amount_minor'=>$invoice->total_minor,'currency_code'=>$invoice->currency_code],
                        'The invoice exceeds the agent authority limit or its source type is not approved for automatic issue.',
                    );
                    $escalated = 1;
                }

                $locked->update([
                    'status'=>'processed',
                    'processed_at'=>now(),
                    'invoice_id'=>$invoice->id,
                    'attempts'=>$locked->attempts+1,
                    'last_error'=>null,
                ]);

                return [
                    'invoice_id'=>$invoice->id,
                    'decision'=>$decision,
                    'status'=>$invoice->status->value,
                    'amount_minor'=>$invoice->total_minor,
                    'acted'=>1,
                    'escalated'=>$escalated,
                ];
            });
        } catch (Throwable $error) {
            $fresh = BillableEvent::query()->withoutGlobalScope('company')->find($event->id);
            $attempts = ((int) ($fresh?->attempts ?? $event->attempts)) + 1;
            $event->update([
                'attempts' => $attempts,
                'status' => $attempts >= 3 ? 'failed' : 'pending',
                'available_at' => now()->addMinutes(5),
                'last_error' => mb_substr($error->getMessage(), 0, 4000),
            ]);
            Log::error('Titan Money invoice agent failed to process a billable event.', [
                'company_id' => $event->company_id,
                'agent_run_id' => $run->id,
                'billable_event_id' => $event->id,
                'correlation_id' => $run->correlation_id,
                'exception' => $error,
            ]);

            return [
                'decision'=>'failed',
                'error'=>'invoice_processing_failed',
                'correlation_id'=>$run->correlation_id,
                'acted'=>0,
                'escalated'=>0,
            ];
        }
    }
}
