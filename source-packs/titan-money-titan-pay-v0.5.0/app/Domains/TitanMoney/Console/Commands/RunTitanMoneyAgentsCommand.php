<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\TitanMoney\Services\TitanMoneyAutomationCoordinator;
use Illuminate\Console\Command;

final class RunTitanMoneyAgentsCommand extends Command
{
    protected $signature = 'titan-money:agents-run {--agent=} {--company=} {--dry-run}';
    protected $description = 'Run enabled Titan Money automation agents with company-scoped authority and audit history.';

    public function handle(TitanMoneyAutomationCoordinator $coordinator): int
    {
        $runs = $coordinator->runEnabled(
            $this->option('agent') ?: null,
            $this->option('company') ?: null,
            (bool) $this->option('dry-run'),
            'command',
        );

        foreach ($runs as $run) {
            $this->line(sprintf('%s: %s — examined %d, acted %d, escalated %d', $run->agent_key, $run->status, $run->items_examined, $run->items_acted, $run->items_escalated));
        }

        $this->info('Completed '.count($runs).' agent run(s).');
        return self::SUCCESS;
    }
}
