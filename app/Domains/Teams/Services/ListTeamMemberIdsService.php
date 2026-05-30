<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListTeamMemberIdsAction;
use App\Domains\Teams\Models\Team;

final class ListTeamMemberIdsService
{
    public function __construct(
        private ListTeamMemberIdsAction $list,
    ) {}

    /**
     * @return array<int, int>
     */
    public function execute(Team $team): array
    {
        return $this->list->execute($team);
    }
}
