<?php

declare(strict_types=1);

use App\Domains\TitanTrain\Presentation\Http\Controllers\Api\AssessmentController;
use App\Domains\TitanTrain\Presentation\Http\Controllers\Api\LearnerController;
use App\Domains\TitanTrain\Presentation\Http\Controllers\Api\ManagementController;
use App\Domains\TitanTrain\Presentation\Http\Controllers\Api\ProgressController;
use App\Domains\TitanTrain\Presentation\Http\Controllers\Api\PwaController;
use Illuminate\Support\Facades\Route;

Route::prefix((string) config('titan_train.api_prefix', 'api/v1/titan-train'))
    ->middleware(['api', 'auth:api', 'workcore.tenant', 'workcore.api', 'throttle:titan-train'])
    ->name('api.titan-train.')
    ->group(function (): void {
        Route::get('pwa/bootstrap', [PwaController::class, 'bootstrap'])->name('pwa.bootstrap');
        Route::get('assignments', [LearnerController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{assignment}', [LearnerController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/lessons/{lesson}/complete', [ProgressController::class, 'complete'])->name('lessons.complete');
        Route::post('assignments/{assignment}/assessments/{assessment}/start', [AssessmentController::class, 'start'])->name('assessments.start');
        Route::post('attempts/{attempt}/submit', [AssessmentController::class, 'submit'])->name('attempts.submit');

        Route::prefix('management')->name('management.')->group(function (): void {
            Route::post('cleaner-foundation/install', [ManagementController::class, 'install'])->name('install');
            Route::post('cleaners/{user}/assign', [ManagementController::class, 'assign'])->name('assign');
            Route::post('assignments/{assignment}/practical-pass', [ManagementController::class, 'practical'])->name('practical');
            Route::post('assignments/{assignment}/finalise', [ManagementController::class, 'finalise'])->name('finalise');
            Route::get('cleaners/{user}/readiness', [ManagementController::class, 'readiness'])->name('readiness');
        });
    });
