<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\Company\Support\ActorContext;
use App\Domains\TitanMoney\Services\InvoiceReminderService;
use Illuminate\Console\Command;

final class DispatchInvoiceRemindersCommand extends Command
{
    protected $signature = 'titan-money:queue-reminders';
    protected $description = 'Queue due invoice reminder events for Titan Channels.';

    public function handle(InvoiceReminderService $service, ActorContext $actorContext): int
    {
        $actorContext->setAgent('titan-money.reminder-agent');
        $count = $service->enqueueDue();
        $this->info("Queued {$count} reminder event(s).");

        return self::SUCCESS;
    }
}
