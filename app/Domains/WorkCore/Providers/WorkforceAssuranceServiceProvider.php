<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\Providers;

use App\Domains\WorkCore\System\Registry\WorkModuleRegistry;
use Illuminate\Support\ServiceProvider;

final class WorkforceAssuranceServiceProvider extends ServiceProvider
{
    private const MODULES = [
        'workforce', 'people', 'attendance_verification', 'rosters', 'attendance', 'compliance',
    ];

    public function register(): void
    {
        $this->app->make(WorkModuleRegistry::class)->loadMany(self::MODULES);
    }
}
