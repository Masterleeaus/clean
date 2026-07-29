<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI\Actions;

use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Services\AutomationPolicyService;
use App\Domains\TitanMoney\Services\ReceivablesFollowUpAgent;
use App\Extensions\AIAgent\System\Actions\Contracts\AIAgentActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;

final class RunReceivablesAgentAction implements AIAgentActionInterface
{
    use ConcernsResolvesAgentCompany;

    public function __construct(
        private readonly AutomationPolicyService $policies,
        private readonly ReceivablesFollowUpAgent $agent,
    ) {}

    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $company = $this->setCompanyForWorkflow($workflow, $config, $run);
        $this->policies->ensureDefaults($company);
        $policy = AutomationPolicy::query()->where('agent_key','receivables_follow_up')->firstOrFail();
        if (! $policy->enabled) {
            $context['titan_money_receivables_agent'] = ['status'=>'skipped','reason'=>'policy_disabled'];
            return $context;
        }
        $agentRun = $this->agent->run($policy, (bool)($config['dry_run'] ?? false), 'ai_workflow');
        $context['titan_money_receivables_agent'] = $agentRun->only(['id','status','items_examined','items_acted','items_escalated']);
        return $context;
    }

    public function getCategory(): string { return 'agents'; }
    public function getLabel(): string { return 'Run Titan Money Receivables Agent'; }
    public function getDescription(): string { return 'Follow up overdue invoices without repeating stages or bypassing approval rules.'; }
    public function getIcon(): string { return 'tabler-bell-dollar'; }
    public function getConfigSchema(): array { return ['type'=>'object','required'=>['company_id'],'properties'=>['company_id'=>['type'=>'string'],'dry_run'=>['type'=>'boolean','default'=>false]]]; }
}
