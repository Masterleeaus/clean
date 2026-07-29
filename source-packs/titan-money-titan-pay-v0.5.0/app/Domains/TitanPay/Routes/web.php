<?php

use App\Domains\TitanPay\Http\Controllers\CollectionSessionController;
use App\Domains\TitanPay\Http\Controllers\GatewayConnectionController;
use App\Domains\TitanPay\Http\Controllers\PaymentClaimController;
use App\Domains\TitanPay\Http\Controllers\PublicPaymentController;
use App\Domains\TitanPay\Http\Controllers\QrCodeController;
use App\Domains\TitanPay\Http\Controllers\TitanPayDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'company.context'])
    ->prefix('titan-pay')
    ->name('titanpay.')
    ->group(function (): void {
        Route::middleware('company.permission:titanpay.read')->group(function (): void {
            Route::get('/', TitanPayDashboardController::class)->name('dashboard');
            Route::get('sessions', [CollectionSessionController::class, 'index'])->name('sessions.index');
            Route::get('sessions/{session}', [CollectionSessionController::class, 'show'])->name('sessions.show');
            Route::get('claims', [PaymentClaimController::class, 'index'])->name('claims.index');
            Route::get('claims/{claim}/evidence/{evidence}', [PaymentClaimController::class, 'evidence'])->name('claims.evidence');
            Route::get('gateways', [GatewayConnectionController::class, 'index'])->name('gateways.index');
        });

        Route::post('sessions', [CollectionSessionController::class, 'store'])
            ->middleware('company.permission:titanpay.session.create')->name('sessions.store');

        Route::middleware('company.permission:titanpay.gateway.manage')->group(function (): void {
            Route::post('gateways', [GatewayConnectionController::class, 'store'])->name('gateways.store');
            Route::put('gateways/{gateway}', [GatewayConnectionController::class, 'update'])->name('gateways.update');
        });

        Route::middleware('company.permission:titanpay.claim.review')->group(function (): void {
            Route::post('claims/{claim}/approve', [PaymentClaimController::class, 'approve'])->name('claims.approve');
            Route::post('claims/{claim}/reject', [PaymentClaimController::class, 'reject'])->name('claims.reject');
        });
    });

Route::middleware(['web', 'throttle:60,1'])
    ->prefix('pay')
    ->name('titanpay.public.')
    ->group(function (): void {
        Route::get('{token}', [PublicPaymentController::class, 'show'])->name('show');
        Route::get('{token}/qr.svg', QrCodeController::class)->middleware('throttle:120,1')->name('qr');
        Route::post('{token}/initiate', [PublicPaymentController::class, 'initiate'])->name('initiate');
        Route::get('{token}/return', [PublicPaymentController::class, 'returned'])->name('return');
        Route::post('{token}/paypal/capture', [PublicPaymentController::class, 'capturePayPal'])->middleware('throttle:20,1')->name('paypal.capture');
        Route::post('{token}/claim', [PublicPaymentController::class, 'claim'])->name('claim');
    });
