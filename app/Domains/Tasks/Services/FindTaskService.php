<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FindTaskService
{
    public function __construct(
        private FindTaskAction $find,
    ) {}

    public function execute(User $actor, int $taskId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('view', $task);

        return $task;
    }
}
