<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\FindActiveTimerForUserAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;

final class FindActiveTimerForUserService
{
    public function __construct(
        private FindActiveTimerForUserAction $find,
    ) {}

    public function execute(User $user): ?TimeEntry
    {
        return $this->find->execute($user);
    }
}
