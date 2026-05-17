<?php

namespace App\Domains\Calendar\Models;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Models\User;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $table = 'calendar_events';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'is_all_day',
        'color',
        'location',
        'recurrence_freq',
        'recurrence_until',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_all_day' => 'boolean',
        'color' => EventColor::class,
        'recurrence_freq' => RecurrenceFreq::class,
        'recurrence_until' => 'date',
    ];

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
