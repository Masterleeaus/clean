<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI\Actions;

use App\Domains\TitanMoney\Services\BillableEventService;
use App\Extensions\AIAgent\System\Actions\Contracts\AIAgentActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;

final class IngestBillableEventAction implements AIAgentActionInterface
{
    use ConcernsResolvesAgentCompany;

    public function __construct(private readonly BillableEventService $events) {}

    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $this->setCompanyForWorkflow($workflow, $config, $run);
        $input = is_array($config['billable_event'] ?? null) ? $config['billable_event'] : ($context['billable_event'] ?? []);
        $event = $this->events->ingest($input);
        $context['titan_money_billable_event'] = ['id'=>$event->id,'status'=>$event->status,'created'=>$event->wasRecentlyCreated];
        return $context;
    }

    public function getCategory(): string { return 'custom'; }
    public function getLabel(): string { return 'Ingest Titan Money Billable Event'; }
    public function getDescription(): string { return 'Accept a governed WorkCore billing source for idempotent agent invoicing.'; }
    public function getIcon(): string { return 'tabler-receipt'; }
    public function getConfigSchema(): array
    {
        return ['type'=>'object','required'=>['company_id','billable_event'],'properties'=>[
            'company_id'=>['type'=>'string','title'=>'Company ID'],
            'billable_event'=>['type'=>'object','title'=>'Billable event payload'],
        ]];
    }
}
