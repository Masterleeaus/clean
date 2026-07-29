<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Providers;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Console\Commands\DispatchInvoiceRemindersCommand;
use App\Domains\TitanMoney\Console\Commands\DispatchTitanMoneyDeliveriesCommand;
use App\Domains\TitanMoney\Console\Commands\PublishTitanMoneyOutboxCommand;
use App\Domains\TitanMoney\Console\Commands\RunTitanMoneyAgentsCommand;
use App\Domains\TitanMoney\AI\Extension\TitanMoneyAIAgentBridge;
use App\Domains\TitanMoney\Console\Commands\GenerateRecurringInvoicesCommand;
use App\Domains\TitanMoney\Console\Commands\ImportInvoixProCommand;
use App\Domains\TitanMoney\Services\ChannelTransportRegistry;
use App\Domains\TitanMoney\Services\Transports\CustomerAppChannelTransport;
use App\Domains\TitanMoney\Services\Transports\EmailChannelTransport;
use App\Domains\TitanMoney\Services\Transports\InternalChannelTransport;
use App\Domains\TitanMoney\Services\Transports\WebhookChannelTransport;
use Illuminate\Support\ServiceProvider;

final class TitanMoneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentCompany::class);
        $this->app->scoped(ActorContext::class);
        $this->mergeConfigFrom(__DIR__.'/../Config/titanmoney.php', 'titanmoney');
        $this->app->singleton(ChannelTransportRegistry::class, function ($app): ChannelTransportRegistry {
            $registry = new ChannelTransportRegistry();
            foreach ([EmailChannelTransport::class, CustomerAppChannelTransport::class, WebhookChannelTransport::class, InternalChannelTransport::class] as $transport) {
                $registry->register($app->make($transport));
            }
            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'titanmoney');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->app->booted(function (): void {
            $this->app->make(TitanMoneyAIAgentBridge::class)->register();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateRecurringInvoicesCommand::class,
                DispatchInvoiceRemindersCommand::class,
                ImportInvoixProCommand::class,
                RunTitanMoneyAgentsCommand::class,
                PublishTitanMoneyOutboxCommand::class,
                DispatchTitanMoneyDeliveriesCommand::class,
            ]);
        }
    }
}
