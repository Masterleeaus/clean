<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'message' => $this->message->message,
            'metadata_json' => $this->message->metadata_json,
            'attachment_path' => $this->message->attachment_path ? true : null,
            'attachment_url' => $this->message->attachment_path ? route('chat.attachments.download', $this->message->id) : null,
            'attachment_name' => $this->message->attachment_name,
            'attachment_type' => $this->message->attachment_type,
            'attachment_size' => $this->message->attachment_size,
            'user' => $this->message->user,
            'created_at' => $this->message->created_at,
        ];
    }
}
