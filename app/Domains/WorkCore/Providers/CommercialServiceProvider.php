<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\Providers;

use App\Domains\WorkCore\System\Registry\WorkModuleRegistry;
use Illuminate\Support\ServiceProvider;

final class CommercialServiceProvider extends ServiceProvider
{
    private const MODULES = ['finance', 'payroll', 'inventory', 'supply', 'vault'];

    public function register(): void
    {
        $this->app->make(WorkModuleRegistry::class)->loadMany(self::MODULES);
    }
}
