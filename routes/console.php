<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('chats:purge-expired')->daily();
Schedule::command('teams:purge-expired-invitations')->daily();
Schedule::command('google-calendar:sync-all')->everyFifteenMinutes();
Schedule::command('notifications:dispatch-reminders')->everyFifteenMinutes();
Schedule::command('notifications:purge-old')->daily();
