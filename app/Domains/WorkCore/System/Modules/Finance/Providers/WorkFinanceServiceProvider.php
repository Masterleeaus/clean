<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Providers;

use App\Domains\WorkCore\System\Actions\{ActionDefinition, BusinessActionRegistry};
use App\Domains\WorkCore\System\Modules\Finance\Actions\{
    AllocateFinanceCredit, AllocateFinanceReceivable, ApproveFinanceExpense,
    ChangeFinanceInvoiceStatus, ChangeFinanceQuoteStatus, CloseFinanceAccountingPeriod,
    CreateFinanceAccount, CreateFinanceAccountingPeriod, CreateFinanceCreditNote,
    CreateFinanceInvoice, CreateFinanceQuote, IssueFinanceInvoice, PostFinanceJournal,
    RecordFinanceExpense
};
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\Modules\Finance\ReadModels\{
    GetFinanceSummary, GetInvoiceProfile, GetQuoteProfile, SearchReceivables
};
use App\Domains\WorkCore\System\Modules\Finance\Repositories\EloquentFinanceRepository;
use App\Domains\WorkCore\System\ReadModels\{ReadModelDefinition, ReadModelRegistry};
use Illuminate\Support\ServiceProvider;

final class WorkFinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FinanceRepositoryContract::class, EloquentFinanceRepository::class);
        $actions = $this->app->make(BusinessActionRegistry::class);
        $definitions = [
            ['workcore.finance.quote.create', CreateFinanceQuote::class, 'medium', 'create_quotes'],
            ['workcore.finance.quote.status', ChangeFinanceQuoteStatus::class, 'high', 'manage_quotes'],
            ['workcore.finance.invoice.create', CreateFinanceInvoice::class, 'medium', 'create_invoices'],
            ['workcore.finance.invoice.issue', IssueFinanceInvoice::class, 'high', 'issue_invoices'],
            ['workcore.finance.invoice.status', ChangeFinanceInvoiceStatus::class, 'critical', 'manage_invoices'],
            ['workcore.finance.credit_note.create', CreateFinanceCreditNote::class, 'high', 'credit_notes'],
            ['workcore.finance.credit.allocate', AllocateFinanceCredit::class, 'critical', 'credit_notes'],
            ['workcore.finance.expense.record', RecordFinanceExpense::class, 'medium', 'expenses'],
            ['workcore.finance.expense.approve', ApproveFinanceExpense::class, 'high', 'approve_expenses'],
            ['workcore.finance.receivable.allocate', AllocateFinanceReceivable::class, 'critical', 'allocate_receivables'],
            ['workcore.finance.account.create', CreateFinanceAccount::class, 'high', 'manage_accounts'],
            ['workcore.finance.period.create', CreateFinanceAccountingPeriod::class, 'high', 'manage_periods'],
            ['workcore.finance.period.close', CloseFinanceAccountingPeriod::class, 'critical', 'manage_periods'],
            ['workcore.finance.journal.post', PostFinanceJournal::class, 'critical', 'post_journals'],
        ];
        foreach ($definitions as [$key, $handler, $risk, $permission]) {
            $actions->register(new ActionDefinition(
                key: $key,
                handler: $handler,
                risk: $risk,
                requiresConfirmation: true,
                capability: 'workcore.finance',
                permission: (string) config("workcore.finance.permissions.{$permission}"),
                metadata: ['domain' => 'finance_accounting', 'canonical_owner' => 'WorkCore Finance'],
            ));
        }

        $reads = $this->app->make(ReadModelRegistry::class);
        $view = (string) config('workcore.finance.permissions.view');
        $reads->register(new ReadModelDefinition('workcore.finance.summary', GetFinanceSummary::class, 'workcore.finance', permission: $view));
        $reads->register(new ReadModelDefinition('workcore.finance.invoice.profile', GetInvoiceProfile::class, 'workcore.finance', permission: $view));
        $reads->register(new ReadModelDefinition('workcore.finance.quote.profile', GetQuoteProfile::class, 'workcore.finance', permission: $view));
        $reads->register(new ReadModelDefinition('workcore.finance.receivables', SearchReceivables::class, 'workcore.finance', permission: $view));
    }
}
