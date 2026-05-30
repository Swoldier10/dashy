<?php

namespace App\Console\Commands;

use App\Domains\GoogleCalendar\Jobs\SyncGoogleCalendarJob;
use App\Domains\GoogleCalendar\Services\ListHealthyConnectionsService;
use Illuminate\Console\Command;

class SyncGoogleCalendarsCommand extends Command
{
    protected $signature = 'google-calendar:sync-all';

    protected $description = 'Dispatch a Google Calendar sync job for every healthy connection.';

    public function handle(ListHealthyConnectionsService $connections): int
    {
        $dispatched = 0;

        foreach ($connections->execute() as $userId) {
            SyncGoogleCalendarJob::dispatch($userId);
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} Google Calendar sync job(s).");

        return self::SUCCESS;
    }
}
