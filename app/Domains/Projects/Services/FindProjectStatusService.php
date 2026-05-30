<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Domains\Projects\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FindProjectStatusService
{
    public function __construct(
        private FindProjectStatusAction $find,
    ) {}

    public function execute(User $actor, int $statusId): ProjectStatus
    {
        $status = $this->find->execute($statusId);

        Gate::forUser($actor)->authorize('view', $status->project);

        return $status;
    }
}
