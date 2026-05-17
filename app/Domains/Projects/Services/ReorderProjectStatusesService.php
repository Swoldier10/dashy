<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\CountProjectStatusesByIdsAction;
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Actions\ReorderProjectStatusesAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ReorderProjectStatusesService
{
    public function __construct(
        private FindProjectAction $find,
        private CountProjectStatusesByIdsAction $count,
        private ReorderProjectStatusesAction $reorder,
    ) {}

    /**
     * @param  list<int|string>  $orderedIds
     */
    public function execute(User $actor, int $projectId, ProjectStatusCategory $category, array $orderedIds): void
    {
        $project = $this->find->execute($projectId);

        Gate::forUser($actor)->authorize('manageStatuses', $project);

        $intIds = array_map(intval(...), $orderedIds);

        if ($intIds === [] || count($intIds) !== count(array_unique($intIds))) {
            throw ValidationException::withMessages([
                'statuses' => __('Invalid status order.'),
            ]);
        }

        $matched = $this->count->execute($project->id, $category, $intIds);

        if ($matched !== count($intIds)) {
            throw ValidationException::withMessages([
                'statuses' => __('Some statuses don\'t belong to this project or category.'),
            ]);
        }

        DB::transaction(fn () => $this->reorder->execute($project->id, $category, $intIds));
    }
}
