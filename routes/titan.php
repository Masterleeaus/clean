<?php

declare(strict_types=1);

use App\Http\Controllers\Titan\CapabilityController;
use App\Http\Controllers\Titan\CompanyContextController;
use App\Http\Controllers\Titan\WorkCoreActionController;
use App\Http\Controllers\Titan\WorkCoreReadController;
use App\Http\Controllers\Titan\WorkCoreSummaryController;
use Illuminate\Support\Facades\Route;

Route::prefix('titan')
    ->middleware(['auth:sanctum', 'titan.company'])
    ->group(function (): void {
        Route::get('/context', [CompanyContextController::class, 'show']);
        Route::post('/context/switch', [CompanyContextController::class, 'switch']);
        Route::get('/summary', WorkCoreSummaryController::class);
        Route::get('/capabilities', CapabilityController::class);
        Route::post('/workcore/actions/{action}', WorkCoreActionController::class)
            ->where('action', '[A-Za-z0-9._:-]+');
        Route::post('/workcore/reads/{read}', WorkCoreReadController::class)
            ->where('read', '[A-Za-z0-9._:-]+');
    });
