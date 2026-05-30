<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Services\FindProjectStatusService;
use App\Domains\Tasks\Actions\ReorderTasksAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ReorderTasksService
{
    public function __construct(
        private FindProjectStatusService $findStatus,
        private ReorderTasksAction $reorder,
    ) {}

    /**
     * Reorder tasks within a status using the post-drop ordering produced by
     * the frontend Sortable instance. Forged or cross-project IDs are
     * silently ignored by ReorderTasksAction's WHERE clauses — the position
     * column for a foreign id is never touched, and the legitimate IDs keep
     * their original input index as their position (gaps are harmless).
     *
     * @param  list<int|string>  $orderedIds
     */
    public function execute(User $actor, int $projectStatusId, array $orderedIds): void
    {
        $status = $this->findStatus->execute($actor, $projectStatusId);

        if ($orderedIds === []) {
            return;
        }

        DB::transaction(function () use ($status, $projectStatusId, $orderedIds) {
            $this->reorder->execute($status->project_id, $projectStatusId, $orderedIds);
        });
    }
}
