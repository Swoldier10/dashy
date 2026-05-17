<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\CreateProjectStatusAction;
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Actions\NextStatusPositionAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class CreateProjectStatusService
{
    public function __construct(
        private FindProjectAction $find,
        private NextStatusPositionAction $next,
        private CreateProjectStatusAction $create,
    ) {}

    public function execute(User $actor, int $projectId, ProjectStatusCategory $category, string $name): ProjectStatus
    {
        $project = $this->find->execute($projectId);

        Gate::forUser($actor)->authorize('manageStatuses', $project);

        $validated = Validator::make(['name' => $name], [
            'name' => ['required', 'string', 'max:60'],
        ])->validate();

        $position = $this->next->execute($project->id, $category);

        return DB::transaction(fn () => $this->create->execute([
            'project_id' => $project->id,
            'category' => $category->value,
            'name' => $validated['name'],
            'position' => $position,
        ]));
    }
}
