<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI;

final class TitanMoneyAgentRegistry
{
    /** @return array<string,array<string,mixed>> */
    public function definitions(): array
    {
        return [
            'titan-money.invoice-agent' => [
                'name' => 'Titan Money Invoice Agent',
                'domain' => 'TitanMoney',
                'role' => 'Turns authorised WorkCore billable events into governed invoice drafts or issued invoices.',
                'permission' => 'titanmoney.agent.run',
                'policy_key' => 'invoice_generation',
                'default_autonomy' => 'draft',
                'tools' => ['titanmoney.ingest_billable_event', 'titanmoney.run_invoice_agent'],
            ],
            'titan-money.receivables-agent' => [
                'name' => 'Titan Money Receivables Agent',
                'domain' => 'TitanMoney',
                'role' => 'Tracks due and overdue balances, schedules follow-ups, and escalates exceptions.',
                'permission' => 'titanmoney.agent.run',
                'policy_key' => 'receivables_follow_up',
                'default_autonomy' => 'bounded',
                'tools' => ['titanmoney.run_receivables_agent', 'titanmoney.list_overdue_invoices'],
            ],
            'titan-pay.reconciliation-agent' => [
                'name' => 'Titan Pay Reconciliation Agent',
                'domain' => 'TitanPay',
                'role' => 'Reviews unmatched verified payment signals without inventing or force-confirming payments.',
                'permission' => 'titanpay.reconciliation.resolve',
                'policy_key' => 'payment_reconciliation',
                'default_autonomy' => 'observe',
                'tools' => ['titanpay.reconcile_transaction'],
            ],
        ];
    }
}
