<?php

namespace App\Domains\Search\Actions;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use RuntimeException;

class FindEmbeddableSourceAction
{
    /**
     * Load the embeddable payload for a (source_type, source_id) pair.
     * Returns null when the source row no longer exists (deleted between
     * dispatch and execution) or contains no embeddable text.
     *
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>}|null
     */
    public function execute(string $sourceType, int $sourceId): ?array
    {
        return match ($sourceType) {
            'task' => $this->loadTask($sourceId),
            'project' => $this->loadProject($sourceId),
            'message' => $this->loadMessage($sourceId),
            default => throw new RuntimeException("Unknown source type [{$sourceType}]."),
        };
    }

    /**
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>}|null
     */
    private function loadTask(int $id): ?array
    {
        $task = Task::query()->with('project:id,team_id,name')->find($id);
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
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>}|null
     */
    private function loadProject(int $id): ?array
    {
        $project = Project::query()->find($id);
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
     * @return array{text: string, team_id: int|null, metadata: array<string, mixed>}|null
     */
    private function loadMessage(int $id): ?array
    {
        $message = Message::query()->with('chat:id,user_id')->find($id);
        if ($message === null) {
            return null;
        }
        $text = trim((string) $message->content);
        if ($text === '' || $message->is_summary) {
            return null;
        }

        return [
            'text' => $text,
            'team_id' => null,
            'metadata' => [
                'user_id' => $message->chat?->user_id,
                'role' => $message->role?->value,
                'chat_id' => $message->chat_id,
            ],
        ];
    }
}
