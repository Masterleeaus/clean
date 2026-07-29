<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $companyId = app(\App\Domains\Company\Support\CurrentCompany::class)->id();

    return $companyId && Conversation::query()
        ->where('company_id', $companyId)
        ->whereKey($conversationId)
        ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
        ->exists();
});

// Presence channel for online users
Broadcast::channel('online.{companyId}', function ($user, $companyId) {
    $currentCompanyId = app(\App\Domains\Company\Support\CurrentCompany::class)->id();
    if ($user && $currentCompanyId && hash_equals((string) $currentCompanyId, (string) $companyId)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
        ];
    }
});
