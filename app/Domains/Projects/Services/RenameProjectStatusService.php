<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Domains\Projects\Actions\UpdateProjectStatusAction;
use App\Domains\Projects\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class RenameProjectStatusService
{
    public function __construct(
        private FindProjectStatusAction $find,
        private UpdateProjectStatusAction $update,
    ) {}

    public function execute(User $actor, int $statusId, string $name): ProjectStatus
    {
        $status = $this->find->execute($statusId);

        Gate::forUser($actor)->authorize('manageStatuses', $status->project);

        $validated = Validator::make(['name' => $name], [
            'name' => ['required', 'string', 'max:60'],
        ])->validate();

        return DB::transaction(fn () => $this->update->execute($status, ['name' => $validated['name']]));
    }
}
