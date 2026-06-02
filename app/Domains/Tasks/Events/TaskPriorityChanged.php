<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

final class TaskPriorityChanged extends TaskEvent
{
    /**
     * @param  list<int>  $assigneeUserIds
     */
    public function __construct(
        TaskSnapshot $task,
        ?int $actorUserId,
        string $actorName,
        public ?string $oldPriority,
        public string $newPriority,
        public array $assigneeUserIds,
    ) {
        parent::__construct($task, $actorUserId, $actorName);
    }

    /**
     * @param  list<int>  $assigneeUserIds
     */
    public static function fromTask(
        Task $task,
        User $actor,
        ?string $oldPriority,
        string $newPriority,
        array $assigneeUserIds,
    ): self {
        return new self(TaskSnapshot::fromTask($task), $actor->id, $actor->name, $oldPriority, $newPriority, $assigneeUserIds);
    }
}
