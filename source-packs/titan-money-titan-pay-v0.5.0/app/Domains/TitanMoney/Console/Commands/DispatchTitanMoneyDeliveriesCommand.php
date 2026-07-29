<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\TitanMoney\Services\ChannelDeliveryDispatcher;
use Illuminate\Console\Command;

final class DispatchTitanMoneyDeliveriesCommand extends Command
{
    protected $signature = 'titan-money:dispatch-deliveries {--limit=}';
    protected $description = 'Deliver due Titan Money invoice and receivables communications through email or Titan Channels.';

    public function handle(ChannelDeliveryDispatcher $dispatcher): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : (int) config('titanmoney.automation.delivery_batch_size', 100);
        $count = $dispatcher->dispatchDue(max(1, $limit));
        $this->info("Dispatched {$count} Titan Money delivery request(s).");
        return self::SUCCESS;
    }
}
