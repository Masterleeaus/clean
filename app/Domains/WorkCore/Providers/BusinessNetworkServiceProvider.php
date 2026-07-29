<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\Providers;

use App\Domains\WorkCore\System\Registry\WorkModuleRegistry;
use Illuminate\Support\ServiceProvider;

final class BusinessNetworkServiceProvider extends ServiceProvider
{
    private const MODULES = [
        'crm', 'catalogue', 'support', 'knowledge', 'reviews', 'territories',
        'intelligence', 'expansion', 'wizards',
    ];

    public function register(): void
    {
        $this->app->make(WorkModuleRegistry::class)->loadMany(self::MODULES);
    }
}
