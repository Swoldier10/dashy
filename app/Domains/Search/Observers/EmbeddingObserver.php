<?php

namespace App\Domains\Search\Observers;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Actions\DeleteEmbeddingAction;
use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps the chat_embeddings table in sync with the source rows that feed
 * semantic_search. Fires on every save/delete of Task/Project/Message; the
 * embedding job is idempotent so re-saves replace in place rather than
 * spawn duplicates.
 */
final class EmbeddingObserver
{
    public function saved(Model $model): void
    {
        $sourceType = $this->typeFor($model);
        if ($sourceType === null) {
            return;
        }

        // Skip messages that carry only a tool call (no embeddable text) and
        // summary placeholders — the job would no-op anyway and we save the
        // queue dispatch cost.
        if ($model instanceof Message && (! $this->shouldEmbedMessage($model))) {
            return;
        }

        EmbedSourceJob::dispatch($sourceType, (int) $model->getKey());
    }

    public function deleted(Model $model): void
    {
        $sourceType = $this->typeFor($model);
        if ($sourceType === null) {
            return;
        }

        app(DeleteEmbeddingAction::class)->execute($sourceType, (int) $model->getKey());
    }

    private function typeFor(Model $model): ?string
    {
        return match (true) {
            $model instanceof Task => 'task',
            $model instanceof Project => 'project',
            $model instanceof Message => 'message',
            default => null,
        };
    }

    private function shouldEmbedMessage(Message $message): bool
    {
        if ($message->is_summary) {
            return false;
        }
        if (trim((string) $message->content) === '') {
            return false;
        }
        if ($message->tool_call !== null) {
            return false;
        }

        return true;
    }
}
