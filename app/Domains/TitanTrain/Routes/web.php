<?php

declare(strict_types=1);

use App\Domains\TitanTrain\Presentation\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'workcore.tenant'])
    ->get('/train', DashboardController::class)
    ->name('titan-train.dashboard');
