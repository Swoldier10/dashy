<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\ReorderTasksAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ReorderTasksService
{
    public function __construct(
        private ReorderTasksAction $reorder,
    ) {}

    /**
     * @param  list<int|string>  $orderedIds
     */
    public function execute(User $actor, int $projectStatusId, array $orderedIds): void
    {
        $status = ProjectStatus::query()->findOrFail($projectStatusId);

        Gate::forUser($actor)->authorize('viewAny', [Task::class, $status->project]);

        DB::transaction(function () use ($status, $projectStatusId, $orderedIds) {
            $this->reorder->execute($status->project_id, $projectStatusId, $orderedIds);
        });
    }
}
