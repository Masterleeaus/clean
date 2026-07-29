<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\Providers;

use App\Domains\WorkCore\System\Registry\WorkModuleRegistry;
use Illuminate\Support\ServiceProvider;

final class WorkOperationsServiceProvider extends ServiceProvider
{
    private const MODULES = [
        'operations', 'scheduling', 'dispatch', 'recurring', 'forms', 'repairs', 'fleet',
    ];

    public function register(): void
    {
        $this->app->make(WorkModuleRegistry::class)->loadMany(self::MODULES);
    }
}
