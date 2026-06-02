<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

final class TaskAttachmentAdded extends TaskEvent
{
    /**
     * @param  list<int>  $assigneeUserIds
     */
    public function __construct(
        TaskSnapshot $task,
        ?int $actorUserId,
        string $actorName,
        public int $attachedCount,
        public array $assigneeUserIds,
    ) {
        parent::__construct($task, $actorUserId, $actorName);
    }

    /**
     * @param  list<int>  $assigneeUserIds
     */
    public static function fromTask(Task $task, User $actor, int $attachedCount, array $assigneeUserIds): self
    {
        return new self(TaskSnapshot::fromTask($task), $actor->id, $actor->name, $attachedCount, $assigneeUserIds);
    }
}
