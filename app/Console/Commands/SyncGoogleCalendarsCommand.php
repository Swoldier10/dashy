<?php

namespace App\Console\Commands;

use App\Domains\GoogleCalendar\Jobs\SyncGoogleCalendarJob;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Illuminate\Console\Command;

class SyncGoogleCalendarsCommand extends Command
{
    protected $signature = 'google-calendar:sync-all';

    protected $description = 'Dispatch a Google Calendar sync job for every healthy connection.';

    public function handle(): int
    {
        $dispatched = 0;

        GoogleCalendarConnection::query()
            ->whereNull('last_sync_error_at')
            ->cursor()
            ->each(function (GoogleCalendarConnection $connection) use (&$dispatched): void {
                SyncGoogleCalendarJob::dispatch($connection->user_id);
                $dispatched++;
            });

        $this->info("Dispatched {$dispatched} Google Calendar sync job(s).");

        return self::SUCCESS;
    }
}
