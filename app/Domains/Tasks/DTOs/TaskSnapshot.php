<?php

namespace App\Domains\Tasks\DTOs;

use App\Domains\Tasks\Models\Task;

/**
 * Queue-safe snapshot of a task's identity at event time. Notification
 * listeners render from this instead of re-querying, so notifications
 * survive later task/project deletion.
 */
final readonly class TaskSnapshot
{
    public function __construct(
        public int $taskId,
        public string $taskName,
        public int $projectId,
        public string $projectName,
        public int $teamId,
    ) {}

    public static function fromTask(Task $task): self
    {
        return new self(
            taskId: $task->id,
            taskName: (string) $task->name,
            projectId: (int) $task->project_id,
            projectName: (string) ($task->project?->name ?? ''),
            teamId: (int) ($task->project?->team_id ?? 0),
        );
    }
}
