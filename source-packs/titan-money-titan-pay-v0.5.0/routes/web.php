<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/chat');
    });

    // Authentication routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:4,1');

});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(IsAdmin::class);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::post('/settings/toggle-notifications', [SettingsController::class, 'toggleNotifications'])->name('settings.toggle-notifications');

    // System Settings
    Route::get('/system-settings', [SettingController::class, 'index'])->name('settings.index')->middleware(IsAdmin::class);
    Route::post('/system-settings', [SettingController::class, 'update'])->name('settings.update')->middleware(IsAdmin::class);

    // User Management CRUD
    Route::resource('users', UserManagementController::class)->middleware('company.permission:company.members.manage');
    Route::post('users/import', [UserManagementController::class, 'import'])->name('users.import')->middleware('company.permission:company.members.manage');

    // Chat routes (for AJAX with web auth)
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
    Route::get('/api/messages/{messageId}/attachment', [ChatController::class, 'attachmentDownload'])->name('chat.attachments.download');
    Route::delete('/api/conversations/{id}', [ChatController::class, 'destroy']);
    Route::get('/api/users', [ChatController::class, 'getUsers']);

    // AI Assistant routes
    Route::post('/api/ai-assistant/message', [ChatController::class, 'aiAssistantMessage']);
    Route::get('/api/ai-assistant/conversation', [ChatController::class, 'getAIConversation']);
    Route::post('/api/conversations/{id}/ai-summary', [ChatController::class, 'aiSummary']);
    Route::post('/api/conversations/{id}/ai-suggestions', [ChatController::class, 'aiSuggestions']);
});
