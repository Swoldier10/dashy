<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleCalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Intentionally empty: v1 has no push-on-save observers; sync is
        // driven by the scheduled command and the manual "Sync now" action.
    }
}
