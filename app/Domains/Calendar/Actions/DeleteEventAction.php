<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\Event;

class DeleteEventAction
{
    public function execute(Event $event): void
    {
        $event->delete();
    }
}
