<?php

namespace App\Console\Commands;

use App\Domains\Chat\Services\PurgeExpiredChatsService;
use Illuminate\Console\Command;

class PurgeExpiredChatsCommand extends Command
{
    protected $signature = 'chats:purge-expired {--days='.PurgeExpiredChatsService::DEFAULT_DAYS.' : Lifespan in days; chats whose updated_at is older than this are deleted}';

    protected $description = 'Delete chats whose last activity is older than the given number of days (default 10).';

    public function handle(PurgeExpiredChatsService $service): int
    {
        $days = (int) $this->option('days');
        if ($days < 0) {
            $this->error('--days must be zero or greater.');

            return self::INVALID;
        }

        $count = $service->execute($days);
        $this->info(sprintf('Purged %d expired chat(s) (lifespan: %d days).', $count, $days));

        return self::SUCCESS;
    }
}
