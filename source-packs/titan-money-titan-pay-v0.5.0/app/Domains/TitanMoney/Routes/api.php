<?php

use App\Domains\TitanMoney\Http\Controllers\Api\AgentRunApiController;
use App\Domains\TitanMoney\Http\Controllers\Api\BillableEventApiController;
use App\Domains\TitanMoney\Http\Controllers\Api\ChannelReceiptController;
use App\Domains\TitanMoney\Http\Controllers\Api\InvoiceApiController;
use Illuminate\Support\Facades\Route;


Route::post('api/titan-money/v1/channel-receipts', ChannelReceiptController::class)
    ->middleware(['api','throttle:120,1'])
    ->name('api.titanmoney.channel-receipts.store');

Route::middleware(['api','auth:sanctum','company.context'])
    ->prefix('api/titan-money/v1')
    ->name('api.titanmoney.')
    ->group(function (): void {
        Route::middleware('company.permission:titanmoney.read')->group(function (): void {
            Route::get('invoices', [InvoiceApiController::class,'index'])->name('invoices.index');
            Route::get('invoices/{invoice}', [InvoiceApiController::class,'show'])->name('invoices.show');
        });

        Route::post('billable-events', [BillableEventApiController::class,'store'])
            ->middleware('company.permission:titanmoney.agent.ingest')->name('billable-events.store');
        Route::post('agents/run', [AgentRunApiController::class,'store'])
            ->middleware('company.permission:titanmoney.agent.run')->name('agents.run');
    });
