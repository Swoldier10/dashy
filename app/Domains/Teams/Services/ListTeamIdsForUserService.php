<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListTeamIdsForUserAction;
use App\Models\User;

final class ListTeamIdsForUserService
{
    public function __construct(
        private ListTeamIdsForUserAction $list,
    ) {}

    /**
     * @return array<int, int>
     */
    public function execute(User $user): array
    {
        return $this->list->execute($user);
    }
}
