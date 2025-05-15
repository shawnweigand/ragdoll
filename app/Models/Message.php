<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class Message extends Model
{
    protected $fillable = ['chat_id', 'source_id', 'role', 'content'];

    protected $casts = [
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function prismMessage()
    {
        switch ($this->role) {
            case 'user':
                return new UserMessage($this->content);
            case 'assistant':
                return new AssistantMessage($this->content);
            case 'system':
                return new SystemMessage($this->content);
        }
    }
}
