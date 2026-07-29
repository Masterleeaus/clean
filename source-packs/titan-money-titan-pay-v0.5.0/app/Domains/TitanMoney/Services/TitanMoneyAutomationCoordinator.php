<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanPay\Services\TitanPayReconciliationAgent;

final class TitanMoneyAutomationCoordinator
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly AutomationPolicyService $policies,
        private readonly InvoiceAutomationAgent $invoiceAgent,
        private readonly ReceivablesFollowUpAgent $receivablesAgent,
        private readonly TitanPayReconciliationAgent $reconciliationAgent,
    ) {}

    /** @return array<int,AgentRun> */
    public function runEnabled(?string $agentKey = null, ?string $companyId = null, bool $dryRun = false, string $triggerType = 'schedule'): array
    {
        $companies = Company::query();
        if ($companyId !== null) {
            $companies->whereKey($companyId);
        }

        foreach ($companies->cursor() as $company) {
            $this->policies->ensureDefaults($company);
        }

        $query = AutomationPolicy::query()->withoutGlobalScope('company')->where('enabled', true);
        if ($agentKey !== null) {
            $query->where('agent_key', $agentKey);
        }
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        $runs = [];
        foreach ($query->orderBy('company_id')->get() as $policy) {
            $company = $policy->company()->withoutGlobalScope('company')->firstOrFail();
            $this->currentCompany->set($company);
            $run = match ($policy->agent_key) {
                'invoice_generation' => $this->invoiceAgent->run($policy, $dryRun, $triggerType),
                'receivables_follow_up' => $this->receivablesAgent->run($policy, $dryRun, $triggerType),
                'payment_reconciliation' => $this->reconciliationAgent->run($policy, $dryRun, $triggerType),
                default => null,
            };
            if ($run instanceof AgentRun) {
                $runs[] = $run;
            }
        }

        return $runs;
    }
}
