<?php

use App\Domains\TitanPay\Http\Controllers\Api\SessionApiController;
use App\Domains\TitanPay\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('api/titan-pay/webhooks/{provider}/{connection}', WebhookController::class)
    ->middleware(['api', 'throttle:120,1'])
    ->whereIn('provider', ['stripe', 'paypal'])
    ->name('titanpay.webhook');

Route::middleware(['api', 'auth:sanctum', 'company.context'])
    ->prefix('api/titan-pay/v1')
    ->group(function (): void {
        Route::middleware('company.permission:titanpay.read')->group(function (): void {
            Route::get('sessions', [SessionApiController::class, 'index']);
            Route::get('sessions/{session}', [SessionApiController::class, 'show']);
        });

        Route::post('sessions', [SessionApiController::class, 'store'])
            ->middleware('company.permission:titanpay.session.create');
    });
