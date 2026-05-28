<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;

class CreateGoogleCalendarConnectionAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): GoogleCalendarConnection
    {
        $connection = new GoogleCalendarConnection;
        $connection->forceFill($attributes);
        $connection->save();

        return $connection->refresh();
    }
}
