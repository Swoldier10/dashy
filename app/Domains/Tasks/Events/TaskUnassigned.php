<?php

namespace App\Domains\Tasks\Events;

use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

final class TaskUnassigned extends TaskEvent
{
    public function __construct(
        TaskSnapshot $task,
        ?int $actorUserId,
        string $actorName,
        public int $removedUserId,
    ) {
        parent::__construct($task, $actorUserId, $actorName);
    }

    public static function fromTask(Task $task, User $actor, int $removedUserId): self
    {
        return new self(TaskSnapshot::fromTask($task), $actor->id, $actor->name, $removedUserId);
    }
}
