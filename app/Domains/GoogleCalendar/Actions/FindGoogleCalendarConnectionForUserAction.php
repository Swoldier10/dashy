<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;

class FindGoogleCalendarConnectionForUserAction
{
    public function execute(User $user): ?GoogleCalendarConnection
    {
        return GoogleCalendarConnection::query()->where('user_id', $user->id)->first();
    }
}
