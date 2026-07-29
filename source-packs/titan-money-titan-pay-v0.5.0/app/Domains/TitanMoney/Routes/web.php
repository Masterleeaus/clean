<?php

use App\Domains\TitanMoney\Http\Controllers\AgentApprovalController;
use App\Domains\TitanMoney\Http\Controllers\AutomationController;
use App\Domains\TitanMoney\Http\Controllers\CustomerController;
use App\Domains\TitanMoney\Http\Controllers\TitanMoneyDashboardController;
use App\Domains\TitanMoney\Http\Controllers\InvoiceController;
use App\Domains\TitanMoney\Http\Controllers\PaymentController;
use App\Domains\TitanMoney\Http\Controllers\ProductController;
use App\Domains\TitanMoney\Http\Controllers\RecurringInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'company.context'])
    ->prefix('titan-money')
    ->name('titanmoney.')
    ->group(function (): void {
        // Static read routes must be declared before dynamic model-binding routes.
        Route::middleware('company.permission:titanmoney.read')->group(function (): void {
            Route::get('/', TitanMoneyDashboardController::class)->name('dashboard');
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('recurring', [RecurringInvoiceController::class, 'index'])->name('recurring.index');
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        });

        Route::get('automation', [AutomationController::class, 'index'])
            ->middleware('company.permission:titanmoney.automation.read')->name('automation.index');
        Route::post('automation/integration', [AutomationController::class, 'updateIntegration'])
            ->middleware('company.permission:titanmoney.automation.manage')->name('automation.integration');
        Route::post('automation/run', [AutomationController::class, 'run'])
            ->middleware('company.permission:titanmoney.automation.manage')->name('automation.run');

        // Static create/store routes precede {model} routes to avoid binding "create" as an ID.
        Route::middleware('company.permission:titanmoney.manage')->group(function (): void {
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('recurring/create', [RecurringInvoiceController::class, 'create'])->name('recurring.create');
            Route::post('recurring', [RecurringInvoiceController::class, 'store'])->name('recurring.store');
        });

        Route::middleware('company.permission:titanmoney.read')->group(function (): void {
            Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
            Route::get('recurring/{recurring}', [RecurringInvoiceController::class, 'show'])->name('recurring.show');
        });

        Route::middleware('company.permission:titanmoney.manage')->group(function (): void {
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
            Route::get('recurring/{recurring}/edit', [RecurringInvoiceController::class, 'edit'])->name('recurring.edit');
            Route::put('recurring/{recurring}', [RecurringInvoiceController::class, 'update'])->name('recurring.update');
            Route::post('recurring/{recurring}/pause', [RecurringInvoiceController::class, 'pause'])->name('recurring.pause');
            Route::post('recurring/{recurring}/resume', [RecurringInvoiceController::class, 'resume'])->name('recurring.resume');
        });

        Route::put('automation/{policy}', [AutomationController::class, 'update'])
            ->middleware('company.permission:titanmoney.automation.manage')->name('automation.update');
        Route::post('automation/approvals/{approval}/approve', [AgentApprovalController::class, 'approve'])
            ->middleware('company.permission:titanmoney.agent.approve')->name('automation.approvals.approve');
        Route::post('automation/approvals/{approval}/reject', [AgentApprovalController::class, 'reject'])
            ->middleware('company.permission:titanmoney.agent.approve')->name('automation.approvals.reject');

        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])
            ->middleware('company.permission:titanmoney.invoice.issue')->name('invoices.issue');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])
            ->middleware('company.permission:titanmoney.invoice.void')->name('invoices.void');
        Route::post('invoices/{invoice}/collection', [InvoiceController::class, 'createCollection'])
            ->middleware('company.permission:titanpay.session.create')->name('invoices.collection');
    });
