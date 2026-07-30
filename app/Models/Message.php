<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
    ];

    protected $hidden = ['attachment_path'];

    protected $appends = ['attachment_url'];

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! is_string($this->attachment_path) || $this->attachment_path === '' || ! $this->exists) {
            return null;
        }

        return URL::temporarySignedRoute(
            'titan.attachments.download',
            now()->addMinutes(30),
            ['message' => $this->getKey()],
        );
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    public function isReadBy($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }
}
