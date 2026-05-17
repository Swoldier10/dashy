<?php

namespace App\Domains\Search\Jobs;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Actions\DeleteEmbeddingAction;
use App\Domains\Search\Actions\UpsertEmbeddingAction;
use App\Domains\Search\Services\EmbedTextService;
use App\Domains\Tasks\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Embed (or re-embed) a single source row. Idempotent on (source_type,
 * source_id) — UpsertEmbeddingAction replaces in place. Dispatched from the
 * Task/Project/Message save listeners and from the dashy:embed-backfill
 * command. Silently no-ops when the source row no longer exists.
 */
class EmbedSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $sourceType,
        public int $sourceId,
    ) {}

    public function handle(
        EmbedTextService $embed,
        UpsertEmbeddingAction $upsert,
        DeleteEmbeddingAction $delete,
    ): void {
        if (! $embed->isConfigured()) {
            return; // No API key — nothing to do.
        }

        $payload = $this->loadSource();
        if ($payload === null) {
            // The source row was deleted before the job ran — clean up.
            $delete->execute($this->sourceType, $this->sourceId);

            return;
        }

        try {
            $vector = $embed->embed($payload['text']);
        } catch (Throwable $e) {
            Log::warning('EmbedSourceJob failed to embed', [
                'source_type' => $this->sourceType,
                'source_id' => $this->sourceId,
                'error' => $e->getMessage(),
            ]);

            throw $e; // let the queue retry per $tries
        }

        $upsert->execute(
            $this->sourceType,
            $this->sourceId,
            $payload['team_id'],
            $payload['text'],
            $vector,
            $payload['metadata'],
        );
    }

    /**
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>|null}|null
     */
    private function loadSource(): ?array
    {
        return match ($this->sourceType) {
            'task' => $this->loadTask(),
            'project' => $this->loadProject(),
            'message' => $this->loadMessage(),
            default => throw new RuntimeException("Unknown source type [{$this->sourceType}]."),
        };
    }

    /**
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>|null}|null
     */
    private function loadTask(): ?array
    {
        $task = Task::with('project:id,team_id,name')->find($this->sourceId);
        if ($task === null) {
            return null;
        }

        $text = trim((string) $task->name."\n\n".(string) $task->description);
        if ($text === '') {
            return null;
        }

        return [
            'text' => $text,
            'team_id' => $task->project?->team_id,
            'metadata' => [
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
                'name' => $task->name,
            ],
        ];
    }

    /**
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>|null}|null
     */
    private function loadProject(): ?array
    {
        $project = Project::find($this->sourceId);
        if ($project === null) {
            return null;
        }

        $text = trim((string) $project->name."\n\n".(string) $project->description);
        if ($text === '') {
            return null;
        }

        return [
            'text' => $text,
            'team_id' => $project->team_id,
            'metadata' => [
                'name' => $project->name,
            ],
        ];
    }

    /**
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>|null}|null
     */
    private function loadMessage(): ?array
    {
        $message = Message::with('chat:id,user_id')->find($this->sourceId);
        if ($message === null) {
            return null;
        }
        $text = trim((string) $message->content);
        if ($text === '' || $message->is_summary) {
            // Skip empty messages and summary placeholders.
            return null;
        }

        return [
            'text' => $text,
            'team_id' => null, // messages live in chats which aren't team-scoped
            'metadata' => [
                'user_id' => $message->chat?->user_id,
                'role' => $message->role?->value,
                'chat_id' => $message->chat_id,
            ],
        ];
    }
}
