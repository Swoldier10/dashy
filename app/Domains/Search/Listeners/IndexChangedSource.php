<?php

namespace App\Domains\Search\Listeners;

use App\Domains\Chat\Events\MessageContentChanged;
use App\Domains\Projects\Events\ProjectContentChanged;
use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Search\Services\EmbedSourceService;
use App\Domains\Tasks\Events\TaskContentChanged;

/**
 * Keeps the chat_embeddings index in sync in response to the owning domains'
 * content-change events. Search depends on those domains' published events —
 * the domains know nothing about Search. Re-embedding is queued (idempotent
 * upsert); deletions remove the row through the transactional service.
 */
final class IndexChangedSource
{
    public function __construct(
        private EmbedSourceService $embedSource,
    ) {}

    public function handle(TaskContentChanged|ProjectContentChanged|MessageContentChanged $event): void
    {
        [$sourceType, $sourceId, $deleted] = match (true) {
            $event instanceof TaskContentChanged => ['task', $event->taskId, $event->deleted],
            $event instanceof ProjectContentChanged => ['project', $event->projectId, $event->deleted],
            $event instanceof MessageContentChanged => ['message', $event->messageId, $event->deleted],
        };

        if ($deleted) {
            $this->embedSource->forget($sourceType, $sourceId);

            return;
        }

        EmbedSourceJob::dispatch($sourceType, $sourceId);
    }
}
