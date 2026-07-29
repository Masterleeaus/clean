<?php

declare(strict_types=1);

use App\Models\CompanyMembership;
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}.{companyId}', function ($user, $id, $companyId): bool {
    $companyId = (int) $companyId;

    return (int) $user->id === (int) $id
        && (int) $user->active_company_id === $companyId
        && CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId): bool {
    $companyId = (int) $user->active_company_id;
    if ($companyId < 1) {
        return false;
    }

    $conversation = Conversation::query()
        ->forCompany($companyId)
        ->find($conversationId);

    return $conversation !== null
        && $conversation->users()->where('users.id', $user->id)->exists();
});

Broadcast::channel('online.{companyId}', function ($user, $companyId): array|false {
    $companyId = (int) $companyId;
    $isMember = (int) $user->active_company_id === $companyId
        && CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

    if (! $isMember) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar,
    ];
});
