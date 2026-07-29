<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'message',
        'metadata_json',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
    ];

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
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
