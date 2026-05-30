<?php

namespace App\Domains\GoogleCalendar\Jobs;

use App\Domains\GoogleCalendar\Services\SyncGoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncGoogleCalendarJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $userId) {}

    public function handle(SyncGoogleCalendarService $sync): void
    {
        $sync->execute($this->userId);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping((string) $this->userId))->expireAfter(300)->dontRelease(),
        ];
    }
}
