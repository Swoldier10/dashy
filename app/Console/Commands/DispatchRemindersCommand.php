<?php

namespace App\Console\Commands;

use App\Domains\Notifications\Services\DispatchScheduledRemindersService;
use Illuminate\Console\Command;

class DispatchRemindersCommand extends Command
{
    protected $signature = 'notifications:dispatch-reminders';

    protected $description = 'Send due-soon, overdue, and event-starting-soon reminder notifications (idempotent).';

    public function handle(DispatchScheduledRemindersService $service): int
    {
        $attempted = $service->execute();
        $this->info(sprintf('Attempted %d reminder delivery(ies).', $attempted));

        return self::SUCCESS;
    }
}
