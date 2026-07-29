<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI\Actions;

use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Services\AutomationPolicyService;
use App\Domains\TitanMoney\Services\InvoiceAutomationAgent;
use App\Extensions\AIAgent\System\Actions\Contracts\AIAgentActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;

final class RunInvoiceAgentAction implements AIAgentActionInterface
{
    use ConcernsResolvesAgentCompany;

    public function __construct(
        private readonly AutomationPolicyService $policies,
        private readonly InvoiceAutomationAgent $agent,
    ) {}

    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $company = $this->setCompanyForWorkflow($workflow, $config, $run);
        $this->policies->ensureDefaults($company);
        $policy = AutomationPolicy::query()->where('agent_key','invoice_generation')->firstOrFail();
        if (! $policy->enabled) {
            $context['titan_money_invoice_agent'] = ['status'=>'skipped','reason'=>'policy_disabled'];
            return $context;
        }
        $agentRun = $this->agent->run($policy, (bool)($config['dry_run'] ?? false), 'ai_workflow');
        $context['titan_money_invoice_agent'] = $agentRun->only(['id','status','items_examined','items_acted','items_escalated']);
        return $context;
    }

    public function getCategory(): string { return 'agents'; }
    public function getLabel(): string { return 'Run Titan Money Invoice Agent'; }
    public function getDescription(): string { return 'Create drafts or issue invoices within the company automation policy and authority limit.'; }
    public function getIcon(): string { return 'tabler-file-invoice'; }
    public function getConfigSchema(): array { return ['type'=>'object','required'=>['company_id'],'properties'=>['company_id'=>['type'=>'string'],'dry_run'=>['type'=>'boolean','default'=>false]]]; }
}
