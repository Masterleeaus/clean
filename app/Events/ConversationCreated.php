<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversation) {}

    /** @return array<int,PrivateChannel> */
    public function broadcastOn(): array
    {
        return $this->conversation->users
            ->map(fn ($user): PrivateChannel => new PrivateChannel(
                'App.Models.User.'.$user->id.'.'.$this->conversation->company_id
            ))
            ->values()
            ->all();
    }

    /** @return array<string,mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'company_id' => $this->conversation->company_id,
            'name' => $this->conversation->name,
            'type' => $this->conversation->type,
            'avatar' => $this->conversation->avatar,
            'updated_at' => $this->conversation->updated_at,
            'latest_message' => $this->conversation->latestMessage,
            'users' => $this->conversation->users,
            'participants' => $this->conversation->users->map(static fn ($user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'is_online' => $user->isOnline(),
            ])->values()->all(),
        ];
    }
}
