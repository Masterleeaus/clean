<?php

declare(strict_types=1);

require __DIR__.'/../app/Domains/TitanMoney/AI/AgentAuthority.php';
require __DIR__.'/../app/Domains/TitanMoney/AI/FollowUpStageResolver.php';
require __DIR__.'/../app/Domains/TitanMoney/AI/TitanMoneyAgentRegistry.php';

use App\Domains\TitanMoney\AI\AgentAuthority;
use App\Domains\TitanMoney\AI\FollowUpStageResolver;
use App\Domains\TitanMoney\AI\TitanMoneyAgentRegistry;

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$message}\nExpected: ".var_export($expected, true)."\nActual: ".var_export($actual, true)."\n");
        exit(1);
    }
}

$authority = new AgentAuthority();

assertSameValue(
    'skip',
    $authority->invoiceDecision(['enabled' => false], 50000, 'completed_job'),
    'Disabled invoice automation must not act.'
);

assertSameValue(
    'draft',
    $authority->invoiceDecision([
        'enabled' => true,
        'autonomy_level' => 'draft',
        'settings' => ['allowed_source_types' => ['completed_job'], 'auto_issue' => true],
        'max_auto_issue_minor' => 100000,
    ], 50000, 'completed_job'),
    'Draft autonomy may create drafts but may not issue.'
);

assertSameValue(
    'issue',
    $authority->invoiceDecision([
        'enabled' => true,
        'autonomy_level' => 'bounded',
        'settings' => ['allowed_source_types' => ['completed_job'], 'auto_issue' => true],
        'max_auto_issue_minor' => 100000,
    ], 50000, 'completed_job'),
    'Bounded autonomy may auto-issue an allowed invoice within its limit.'
);

assertSameValue(
    'approval',
    $authority->invoiceDecision([
        'enabled' => true,
        'autonomy_level' => 'bounded',
        'settings' => ['allowed_source_types' => ['completed_job'], 'auto_issue' => true],
        'max_auto_issue_minor' => 100000,
    ], 150000, 'completed_job'),
    'Invoices above the agent authority limit must require approval.'
);

assertSameValue(
    'approval',
    $authority->invoiceDecision([
        'enabled' => true,
        'autonomy_level' => 'full',
        'settings' => ['allowed_source_types' => ['approved_quote'], 'auto_issue' => true],
        'max_auto_issue_minor' => 500000,
    ], 90000, 'completed_job'),
    'An unapproved source type must not be auto-issued.'
);

$resolver = new FollowUpStageResolver();
$stages = [
    ['key' => 'due_today', 'days_overdue' => 0],
    ['key' => 'overdue_3', 'days_overdue' => 3],
    ['key' => 'overdue_7', 'days_overdue' => 7],
    ['key' => 'overdue_14', 'days_overdue' => 14, 'approval_required' => true],
];

assertSameValue('overdue_7', $resolver->resolve(9, $stages, []), 'The latest eligible follow-up stage must be selected.');
assertSameValue(null, $resolver->resolve(9, $stages, ['overdue_7']), 'After a later stage is completed, earlier stages must never be backfilled.');
assertSameValue('overdue_7', $resolver->resolve(9, $stages, ['overdue_3']), 'Stage progression may advance from an earlier completed stage to the latest eligible stage.');
assertSameValue(null, $resolver->resolve(-1, $stages, []), 'No overdue follow-up may be selected before the due date.');

$registry = new TitanMoneyAgentRegistry();
$agents = $registry->definitions();
assertSameValue(true, isset($agents['titan-money.invoice-agent']), 'Invoice agent must be registered.');
assertSameValue(true, isset($agents['titan-money.receivables-agent']), 'Receivables agent must be registered.');
assertSameValue(true, isset($agents['titan-pay.reconciliation-agent']), 'Reconciliation agent must be registered.');
assertSameValue('titanmoney.agent.run', $agents['titan-money.invoice-agent']['permission'], 'Invoice agent permission must be explicit.');

fwrite(STDOUT, "GREEN: Titan Money agent authority, follow-up staging and registry invariants passed.\n");
