<?php

namespace App\Domains\Notifications\Models;

use App\Domains\Notifications\Enums\NotificationType;
use App\Models\User;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'actor_user_id',
        'type',
        'subject_type',
        'subject_id',
        'data',
        'dedupe_key',
        'read_at',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
