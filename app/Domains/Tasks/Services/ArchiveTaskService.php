<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ArchiveTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    public function execute(User $actor, int $taskId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        return DB::transaction(fn () => $this->update->execute($task, ['is_archived' => true]));
    }
}
