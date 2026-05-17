<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\Event;

class UpdateEventAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Event $event, array $attributes): Event
    {
        $event->fill($attributes);
        $event->save();

        return $event->refresh();
    }
}
