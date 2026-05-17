<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\Event;

class CreateEventAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Event
    {
        $event = Event::create($attributes);

        return $event->refresh();
    }
}
