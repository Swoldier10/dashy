<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListTeamsForUserAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListTeamsForUserService
{
    public function __construct(private readonly ListTeamsForUserAction $action) {}

    /**
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return $this->action->execute($user);
    }
}
