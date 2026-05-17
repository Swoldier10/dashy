<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\ListActiveTimersForUserTeamsAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Who is currently running a timer across any team the actor belongs to. The
 * caller sees the timer owner (id + name) and the task they're working on.
 * Useful for the AI chat to answer "who is on what right now?" without
 * leaking timers from outside the actor's team graph.
 */
final class WhoIsWorkingOnService
{
    public function __construct(
        private ListActiveTimersForUserTeamsAction $listActive,
    ) {}

    /**
     * @return Collection<int, TimeEntry>
     */
    public function execute(User $actor): Collection
    {
        return $this->listActive->execute($actor);
    }
}
