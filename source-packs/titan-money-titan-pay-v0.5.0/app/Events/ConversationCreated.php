<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        // Broadcast to each participant's private channel
        return $this->conversation->users->map(function ($user) {
            return new PrivateChannel('App.Models.User.'.$user->id);
        })->toArray();
    }

    public function broadcastWith(): array
    {
        // Format the data similar to how formatConversation does in ChatController
        // Since we can't easily access the auth user here (it varies per recipient),
        // we send raw data and let backend/frontend handle logic, or send fully populated data
        // For simplicity and consistency, we send basics and let frontend format

        // Actually, we must send the 'other_participant' logic, but that depends on WHO is viewing.
        // So we send the participants list, and frontend derives 'other_participant'.

        return [
            'id' => $this->conversation->id,
            'name' => $this->conversation->name,
            'type' => $this->conversation->type,
            'avatar' => $this->conversation->avatar,
            'updated_at' => $this->conversation->updated_at,
            'latest_message' => $this->conversation->latestMessage,
            'users' => $this->conversation->users, // Include users so frontend can find 'other_participant'
            'participants' => $this->conversation->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_online' => $user->isOnline(),
                ];
            }),
        ];
    }
}
