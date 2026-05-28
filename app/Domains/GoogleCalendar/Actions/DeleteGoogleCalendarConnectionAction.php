<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;

class DeleteGoogleCalendarConnectionAction
{
    public function execute(GoogleCalendarConnection $connection): void
    {
        $connection->delete();
    }
}
