<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\Event;

class FindEventAction
{
    public function execute(int $eventId): Event
    {
        return Event::query()->findOrFail($eventId);
    }
}
