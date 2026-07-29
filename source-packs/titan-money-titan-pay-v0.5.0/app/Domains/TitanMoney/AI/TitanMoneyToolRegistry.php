<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI;

final class TitanMoneyToolRegistry
{
    /** @return array<string,array<string,mixed>> */
    public function tools(): array
    {
        return [
            'titanmoney.create_invoice_draft' => ['permission'=>'titanmoney.manage','approval'=>'none','reversible'=>true],
            'titanmoney.preview_invoice' => ['permission'=>'titanmoney.read','approval'=>'none','reversible'=>true],
            'titanmoney.issue_invoice' => ['permission'=>'titanmoney.invoice.issue','approval'=>'required','reversible'=>false],
            'titanmoney.void_invoice' => ['permission'=>'titanmoney.invoice.void','approval'=>'required','reversible'=>false],
            'titanmoney.list_outstanding_invoices' => ['permission'=>'titanmoney.read','approval'=>'none','reversible'=>true],
            'titanmoney.list_overdue_invoices' => ['permission'=>'titanmoney.read','approval'=>'none','reversible'=>true],
            'titanmoney.create_credit_note' => ['permission'=>'titanmoney.credit.create','approval'=>'required','reversible'=>false],
            'titanmoney.generate_statement' => ['permission'=>'titanmoney.read','approval'=>'none','reversible'=>true],
            'titanmoney.ingest_billable_event' => [
                'permission'=>'titanmoney.agent.ingest','approval'=>'none','reversible'=>true,
                'idempotent'=>true,'agent'=>'titan-money.invoice-agent',
            ],
            'titanmoney.run_invoice_agent' => [
                'permission'=>'titanmoney.agent.run','approval'=>'policy','reversible'=>true,
                'agent'=>'titan-money.invoice-agent',
            ],
            'titanmoney.run_receivables_agent' => [
                'permission'=>'titanmoney.agent.run','approval'=>'policy','reversible'=>true,
                'agent'=>'titan-money.receivables-agent',
            ],
            'titanmoney.approve_agent_action' => [
                'permission'=>'titanmoney.agent.approve','approval'=>'required','reversible'=>false,
            ],
        ];
    }
}
