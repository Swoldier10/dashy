<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Base for all task notification events: a queue-safe task snapshot plus the
 * acting user. Emitted by the Tasks domain after commit; the Notifications
 * domain listens — Tasks knows nothing about Notifications.
 */
abstract class TaskEvent
{
    use Dispatchable;

    public function __construct(
        public TaskSnapshot $task,
        public ?int $actorUserId,
        public string $actorName,
    ) {}

    /**
     * The shared notification data snapshot; listeners merge type-specific
     * fields on top.
     *
     * @return array<string, mixed>
     */
    public function baseData(): array
    {
        return [
            'task_id' => $this->task->taskId,
            'task_name' => $this->task->taskName,
            'project_id' => $this->task->projectId,
            'project_name' => $this->task->projectName,
            'actor_name' => $this->actorName,
        ];
    }
}
