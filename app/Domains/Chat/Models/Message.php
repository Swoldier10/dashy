<?php

namespace App\Domains\Chat\Models;

use App\Domains\Chat\Enums\MessageRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['chat_id', 'parent_user_message_id', 'role', 'content', 'attachments', 'tool_call', 'is_summary'];

    protected $touches = ['chat'];

    protected $casts = [
        'role' => MessageRole::class,
        'attachments' => 'array',
        'tool_call' => 'array',
        'is_summary' => 'boolean',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function parentUserMessage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_user_message_id');
    }
}
