<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\Company\Support\ActorContext;
use App\Domains\TitanMoney\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

final class GenerateRecurringInvoicesCommand extends Command
{
    protected $signature = 'titan-money:generate-recurring';
    protected $description = 'Queue due recurring Titan Money billable events idempotently.';

    public function handle(RecurringInvoiceService $service, ActorContext $actorContext): int
    {
        $actorContext->setAgent('titan-money.recurring-invoice-agent');
        $count = $service->generateDue();
        $this->info("Queued {$count} recurring billable event(s).");

        return self::SUCCESS;
    }
}
