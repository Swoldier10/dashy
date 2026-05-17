<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\ListProjectStatusesForUserAction;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListProjectStatusesForUserService
{
    public function __construct(
        private ListProjectStatusesForUserAction $list,
    ) {}

    /**
     * @return Collection<int, ProjectStatus>
     */
    public function execute(User $actor, ?Team $team = null): Collection
    {
        return $this->list->execute($actor, $team);
    }
}
