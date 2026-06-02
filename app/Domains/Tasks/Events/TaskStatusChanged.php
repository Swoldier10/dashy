<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

final class TaskStatusChanged extends TaskEvent
{
    /**
     * @param  list<int>  $assigneeUserIds
     */
    public function __construct(
        TaskSnapshot $task,
        ?int $actorUserId,
        string $actorName,
        public ?string $oldStatusName,
        public ?string $oldCategory,
        public string $newStatusName,
        public string $newCategory,
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
        ?string $oldStatusName,
        ?string $oldCategory,
        string $newStatusName,
        string $newCategory,
        array $assigneeUserIds,
    ): self {
        return new self(
            TaskSnapshot::fromTask($task),
            $actor->id,
            $actor->name,
            $oldStatusName,
            $oldCategory,
            $newStatusName,
            $newCategory,
            $assigneeUserIds,
        );
    }

    public function becameDone(): bool
    {
        return $this->newCategory === 'done' && $this->oldCategory !== 'done';
    }
}
