<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI\Extension;

use App\Domains\TitanMoney\AI\Actions\IngestBillableEventAction;
use App\Domains\TitanMoney\AI\Actions\RunInvoiceAgentAction;
use App\Domains\TitanMoney\AI\Actions\RunReceivablesAgentAction;

final class TitanMoneyAIAgentBridge
{
    public function register(): void
    {
        $registryClass = 'App\\Extensions\\AIAgent\\System\\Engine\\AIAgentActionRegistry';
        $actionContract = 'App\\Extensions\\AIAgent\\System\\Actions\\Contracts\\AIAgentActionInterface';
        $workflowClass = 'App\\Extensions\\AIAgent\\System\\Models\\AIAgentWorkflow';
        $workflowRunClass = 'App\\Extensions\\AIAgent\\System\\Models\\AIAgentWorkflowRun';

        if (
            ! class_exists($registryClass)
            || ! interface_exists($actionContract)
            || ! class_exists($workflowClass)
            || ! class_exists($workflowRunClass)
        ) {
            return;
        }

        $registry = app($registryClass);
        $registry->register('titan_money_ingest_billable_event', IngestBillableEventAction::class);
        $registry->register('titan_money_run_invoice_agent', RunInvoiceAgentAction::class);
        $registry->register('titan_money_run_receivables_agent', RunReceivablesAgentAction::class);
    }
}
