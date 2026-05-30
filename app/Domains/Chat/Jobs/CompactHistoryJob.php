<?php

namespace App\Domains\Chat\Jobs;

use App\Domains\Chat\Services\CompactHistoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Background entry point for chat compaction. Dispatched after a chat
 * crosses the message-count threshold; the service decides whether enough
 * head remains to be worth summarising. Idempotent — multiple dispatches
 * just produce overlapping summaries that LlmInputBuilder reconciles by
 * keeping the most recent.
 */
class CompactHistoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $chatId) {}

    public function handle(CompactHistoryService $compact): void
    {
        $compact->execute($this->chatId);
    }
}
