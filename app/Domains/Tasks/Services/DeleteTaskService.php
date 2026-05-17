<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\DeleteTaskAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DeleteTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private DeleteTaskAction $delete,
    ) {}

    public function execute(User $actor, int $taskId): void
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('delete', $task);

        DB::transaction(fn () => $this->delete->execute($task));
    }
}
