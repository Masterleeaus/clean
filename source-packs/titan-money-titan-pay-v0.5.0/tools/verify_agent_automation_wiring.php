<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$mustExist = [
    'app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php',
    'app/Domains/TitanMoney/Services/ReceivablesFollowUpAgent.php',
    'app/Domains/TitanPay/Services/TitanPayReconciliationAgent.php',
    'app/Domains/TitanMoney/Services/TitanMoneyAutomationCoordinator.php',
    'app/Domains/TitanMoney/Services/TitanMoneyOutboxPublisher.php',
    'app/Domains/TitanMoney/Services/ChannelDeliveryDispatcher.php',
    'app/Domains/TitanMoney/Services/BillableEventService.php',
    'app/Domains/TitanMoney/AI/Extension/TitanMoneyAIAgentBridge.php',
    'app/Domains/TitanMoney/Database/Migrations/2026_07_27_000030_create_titan_money_automation_tables.php',
    'app/Domains/TitanMoney/Resources/views/automation/index.blade.php',
    'docs/TITAN_MONEY_AGENT_AUTOMATION.md',
];
foreach ($mustExist as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = 'Missing '.$file;
    }
}

function fileContains(string $root, string $file, array $needles, array &$errors): void
{
    $content = is_file($root.'/'.$file) ? file_get_contents($root.'/'.$file) : '';
    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            $errors[] = "Missing {$needle} in {$file}";
        }
    }
}


fileContains($root, 'bootstrap/app.php', [
    'prependToPriorityList(SubstituteBindings::class, ResolveCompanyContext::class)',
], $errors);

fileContains($root, 'bootstrap/providers.php', [
    'TitanMoneyServiceProvider',
    'TitanPayServiceProvider',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Providers/TitanMoneyServiceProvider.php', [
    'RunTitanMoneyAgentsCommand::class',
    'PublishTitanMoneyOutboxCommand::class',
    'DispatchTitanMoneyDeliveriesCommand::class',
    'TitanMoneyAIAgentBridge::class',
], $errors);

fileContains($root, 'routes/console.php', [
    'titan-money:agents-run',
    'titan-money:publish-outbox',
    'titan-money:dispatch-deliveries',
    'titan-money:generate-recurring',
    'titan-money:queue-reminders',
    'withoutOverlapping()',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Routes/api.php', [
    "Route::post('billable-events'",
    'titanmoney.agent.ingest',
    "Route::post('agents/run'",
    'titanmoney.agent.run',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Routes/web.php', [
    "Route::get('automation'",
    'titanmoney.automation.read',
    'titanmoney.automation.manage',
    'titanmoney.agent.approve',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php', [
    "public const AGENT_ID = 'titan-money.invoice-agent'",
    "'source_idempotency_key' => \$locked->idempotency_key",
    '$this->approvals->request(',
    '$this->invoices->issue($invoice)',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/ReceivablesFollowUpAgent.php', [
    "public const AGENT_ID = 'titan-money.receivables-agent'",
    'if ($daysOverdue > 0)',
    "whereIn('status',['queued','sent','handed_off','approval_pending'])",
    '$this->approvals->request(',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/RecurringInvoiceService.php', [
    '$this->billableEvents->ingest([',
    "'source_type'=>'recurring_obligation'",
    "'idempotency_key'",
    '$idempotency',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/TitanMoneyOutboxPublisher.php', [
    "'invoice.issued'",
    "'invoice.reminder_due'",
    'sendOnIssueEnabled',
    'queueReminder(',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/ChannelDeliveryDispatcher.php', [
    'ChannelTransportRegistry',
    '$this->transports->for(',
    'TitanChannelsDeliveryRequested',
    'markReminderComplete',
    '$attempts >= 5',
], $errors);

fileContains($root, 'app/Domains/TitanMoney/Services/AutomationPolicyService.php', [
    "'invoice_generation' => [\n                'enabled' => true",
    "'receivables_follow_up' => [\n                'enabled' => true",
    "'payment_reconciliation' => [\n                'enabled' => false",
], $errors);

$migration = file_get_contents($root.'/app/Domains/TitanMoney/Database/Migrations/2026_07_27_000030_create_titan_money_automation_tables.php');
foreach ([
    'titan_money_automation_policies',
    'titan_money_billable_events',
    'titan_money_agent_runs',
    'titan_money_agent_approval_requests',
    'titan_money_invoice_follow_ups',
    'titan_money_channel_deliveries',
] as $table) {
    if (! str_contains($migration, $table)) {
        $errors[] = 'Missing automation table '.$table;
    }
}

$publicRoutes = file_get_contents($root.'/app/Domains/TitanPay/Routes/web.php');
if (preg_match('/Route::get\([^\n]*(success|complete|paid)/i', $publicRoutes)) {
    $errors[] = 'Public GET route can appear to confirm payment.';
}

$agentFiles = [
    'app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php',
    'app/Domains/TitanMoney/Services/ReceivablesFollowUpAgent.php',
    'app/Domains/TitanPay/Services/TitanPayReconciliationAgent.php',
];
foreach ($agentFiles as $file) {
    $content = file_get_contents($root.'/'.$file);
    if (preg_match('/->update\(\[[^\]]*[\'\"]status[\'\"]\s*=>\s*[\'\"]paid[\'\"]/s', $content)) {
        $errors[] = 'Agent directly marks a record paid in '.$file;
    }
}

if ($errors !== []) {
    fwrite(STDERR, "FAIL\n- ".implode("\n- ", array_values(array_unique($errors)))."\n");
    exit(1);
}

echo "GREEN: agent automation providers, routes, scheduler, governance, outbox and delivery wiring pass structural verification.\n";
