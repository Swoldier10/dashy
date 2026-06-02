<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

final class TaskDueDateChanged extends TaskEvent
{
    /**
     * @param  list<int>  $assigneeUserIds
     */
    public function __construct(
        TaskSnapshot $task,
        ?int $actorUserId,
        string $actorName,
        public ?string $oldEndDate,
        public ?string $newEndDate,
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
        ?string $oldEndDate,
        ?string $newEndDate,
        array $assigneeUserIds,
    ): self {
        return new self(TaskSnapshot::fromTask($task), $actor->id, $actor->name, $oldEndDate, $newEndDate, $assigneeUserIds);
    }
}
