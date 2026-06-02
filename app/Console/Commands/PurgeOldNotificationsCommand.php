<?php

namespace App\Console\Commands;

use App\Domains\Notifications\Services\PurgeOldNotificationsService;
use Illuminate\Console\Command;

class PurgeOldNotificationsCommand extends Command
{
    protected $signature = 'notifications:purge-old';

    protected $description = 'Delete read notifications older than 30 days and any notification older than 90 days.';

    public function handle(PurgeOldNotificationsService $service): int
    {
        $count = $service->execute();
        $this->info(sprintf('Purged %d old notification(s).', $count));

        return self::SUCCESS;
    }
}
