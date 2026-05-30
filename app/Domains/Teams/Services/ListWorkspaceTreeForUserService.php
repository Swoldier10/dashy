<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListWorkspaceTreeForUserAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public read of the user's workspace tree (teams → members + projects →
 * statuses). Consumed by cross-domain callers such as the AI context builder
 * so they never query the Teams/Projects tables themselves.
 */
final class ListWorkspaceTreeForUserService
{
    public function __construct(
        private ListWorkspaceTreeForUserAction $list,
    ) {}

    /**
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return $this->list->execute($user);
    }
}
