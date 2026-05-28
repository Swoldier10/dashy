<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;

class UpdateGoogleCalendarConnectionAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(GoogleCalendarConnection $connection, array $attributes): GoogleCalendarConnection
    {
        $connection->forceFill($attributes)->save();

        return $connection->refresh();
    }
}
