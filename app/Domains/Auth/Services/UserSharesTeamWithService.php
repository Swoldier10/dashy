<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\UserSharesTeamWithAction;
use App\Models\User;

final class UserSharesTeamWithService
{
    public function __construct(
        private UserSharesTeamWithAction $sharesTeam,
    ) {}

    public function execute(User $candidate, User $actor): bool
    {
        return $this->sharesTeam->execute($candidate, $actor);
    }
}
