<?php

namespace App\Domains\Calendar\DTOs;

use App\Domains\Calendar\Models\Event;
use Carbon\CarbonImmutable;

final class EventOccurrence
{
    public function __construct(
        public readonly Event $event,
        public readonly CarbonImmutable $startAt,
        public readonly CarbonImmutable $endAt,
    ) {}

    public function key(): string
    {
        return $this->event->id.'@'.$this->startAt->getTimestamp();
    }
}
