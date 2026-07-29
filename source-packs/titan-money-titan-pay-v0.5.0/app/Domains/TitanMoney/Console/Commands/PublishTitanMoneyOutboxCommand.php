<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\TitanMoney\Services\TitanMoneyOutboxPublisher;
use Illuminate\Console\Command;

final class PublishTitanMoneyOutboxCommand extends Command
{
    protected $signature = 'titan-money:publish-outbox {--limit=}';
    protected $description = 'Publish Titan Money domain events and queue Titan Channels deliveries.';

    public function handle(TitanMoneyOutboxPublisher $publisher): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : (int) config('titanmoney.automation.outbox_batch_size', 200);
        $count = $publisher->publishPending(max(1, $limit));
        $this->info("Published {$count} Titan Money outbox event(s).");
        return self::SUCCESS;
    }
}
