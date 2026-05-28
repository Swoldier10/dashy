<?php

namespace App\Domains\GoogleCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GoogleCalendarLink extends Model
{
    protected $fillable = [
        'connection_id',
        'syncable_type',
        'syncable_id',
        'google_event_id',
        'etag',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(GoogleCalendarConnection::class, 'connection_id');
    }

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }
}
