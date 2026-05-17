<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\ListRecentTimeEntriesForUserAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListRecentTimeEntriesForUserService
{
    public function __construct(
        private ListRecentTimeEntriesForUserAction $listRecent,
    ) {}

    /**
     * @return Collection<int, TimeEntry>
     */
    public function execute(User $actor, int $limit = 25): Collection
    {
        return $this->listRecent->execute($actor, $limit);
    }
}
