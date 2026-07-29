<?php

use App\Http\Middleware\RequireCompanyPermission;
use App\Http\Middleware\ResolveCompanyContext;
use App\Http\Middleware\UpdateUserLastSeen;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->web(append: [ResolveCompanyContext::class, UpdateUserLastSeen::class]);
        $middleware->alias([
            'company.context' => ResolveCompanyContext::class,
            'company.permission' => RequireCompanyPermission::class,
        ]);
        $middleware->prependToPriorityList(SubstituteBindings::class, ResolveCompanyContext::class);
        $middleware->validateCsrfTokens(except: [
            'api/titan-pay/webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
