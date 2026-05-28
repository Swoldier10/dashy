<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;

class DeleteGoogleCalendarLinkAction
{
    public function execute(GoogleCalendarLink $link): void
    {
        $link->delete();
    }
}
