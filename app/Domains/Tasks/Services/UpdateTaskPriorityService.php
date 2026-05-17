<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateTaskPriorityService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    public function execute(User $actor, int $taskId, string $priority): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $validated = Validator::make(['priority' => $priority], [
            'priority' => ['required', Rule::enum(TaskPriority::class)],
        ])->validate();

        return DB::transaction(fn () => $this->update->execute($task, [
            'priority' => $validated['priority'],
        ]));
    }
}
