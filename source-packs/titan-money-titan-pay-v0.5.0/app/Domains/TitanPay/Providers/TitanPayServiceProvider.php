<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Providers;

use App\Domains\TitanPay\Adapters\BankTransferGatewayAdapter;
use App\Domains\TitanPay\Adapters\CashGatewayAdapter;
use App\Domains\TitanPay\Adapters\PayIdGatewayAdapter;
use App\Domains\TitanPay\Adapters\PayPalGatewayAdapter;
use App\Domains\TitanPay\Adapters\StripeGatewayAdapter;
use App\Domains\TitanPay\Contracts\SecretResolver;
use App\Domains\TitanPay\Security\StripeWebhookVerifier;
use App\Domains\TitanPay\Services\GatewayRegistry;
use App\Domains\TitanPay\Support\EnvironmentSecretResolver;
use Illuminate\Support\ServiceProvider;

final class TitanPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/titanpay.php','titanpay');
        $this->app->bind(SecretResolver::class,EnvironmentSecretResolver::class);
        $this->app->singleton(StripeWebhookVerifier::class,fn()=>new StripeWebhookVerifier((int)config('titanpay.stripe_tolerance_seconds',300)));
        $this->app->singleton(GatewayRegistry::class,function($app){
            $registry=new GatewayRegistry();
            foreach([CashGatewayAdapter::class,PayIdGatewayAdapter::class,BankTransferGatewayAdapter::class,StripeGatewayAdapter::class,PayPalGatewayAdapter::class] as $class) $registry->register($app->make($class));
            return $registry;
        });
    }
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views','titanpay');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
