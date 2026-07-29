<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Titan\AiConfigurationController;
use App\Http\Controllers\Titan\CompanySetupController;
use App\Http\Controllers\Titan\MessageAttachmentController;
use App\Http\Controllers\Titan\OperationsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Middleware\IsAdmin;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', static fn () => redirect('/chat'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/titan/company/setup', [CompanySetupController::class, 'create'])
        ->name('titan.company.setup');
    Route::post('/titan/company/setup', [CompanySetupController::class, 'store'])
        ->name('titan.company.setup.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware(['titan.company', IsAdmin::class]);

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::post('/settings/toggle-notifications', [SettingsController::class, 'toggleNotifications'])->name('settings.toggle-notifications');

    Route::get('/system-settings', [SettingController::class, 'index'])
        ->name('settings.index')
        ->middleware(IsAdmin::class);
    Route::post('/system-settings', [SettingController::class, 'update'])
        ->name('settings.update')
        ->middleware(IsAdmin::class);

    Route::resource('users', UserManagementController::class)->middleware(IsAdmin::class);
    Route::post('users/import', [UserManagementController::class, 'import'])
        ->name('users.import')
        ->middleware(IsAdmin::class);
});

Route::middleware(['auth', 'titan.company'])->group(function (): void {
    Route::get('/titan/operations', OperationsController::class)->name('titan.operations');
    Route::post('/titan/ai-configuration', AiConfigurationController::class)->name('titan.ai.configure');
    Route::get('/titan/attachments/{message}', MessageAttachmentController::class)
        ->middleware('signed')
        ->name('titan.attachments.download');

    Route::get('/chat', static function (ActiveCompanyContext $context) {
        return view('chat', ['activeCompanyId' => $context->companyId]);
    })->name('chat');

    Route::get('/api/conversations', [ChatController::class, 'index']);
    Route::post('/api/conversations', [ChatController::class, 'store']);
    Route::get('/api/conversations/{id}', [ChatController::class, 'show']);
    Route::put('/api/conversations/{id}', [ChatController::class, 'update']);
    Route::get('/api/conversations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/api/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/api/conversations/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/api/conversations/{id}/typing', [ChatController::class, 'typing']);
    Route::post('/api/conversations/{id}/participants', [ChatController::class, 'addParticipants']);
    Route::post('/api/conversations/{id}/participants/{userId}/remove', [ChatController::class, 'removeParticipant']);
    Route::post('/api/conversations/{id}/leave', [ChatController::class, 'leave']);
    Route::post('/api/messages/{id}/read', [ChatController::class, 'markMessageAsRead']);
    Route::delete('/api/conversations/{id}', [ChatController::class, 'destroy']);
    Route::get('/api/users', [ChatController::class, 'getUsers']);

    Route::post('/api/ai-assistant/message', [ChatController::class, 'aiAssistantMessage']);
    Route::get('/api/ai-assistant/conversation', [ChatController::class, 'getAIConversation']);
    Route::post('/api/conversations/{id}/ai-summary', [ChatController::class, 'aiSummary']);
    Route::post('/api/conversations/{id}/ai-suggestions', [ChatController::class, 'aiSuggestions']);
});
