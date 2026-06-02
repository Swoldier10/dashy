<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\DeleteNotificationsForUserInTeamAction;
use Illuminate\Support\Facades\DB;

final class DeleteTeamNotificationsForUserService
{
    public function __construct(
        private DeleteNotificationsForUserInTeamAction $delete,
    ) {}

    public function execute(int $userId, int $teamId): int
    {
        return DB::transaction(fn () => $this->delete->execute($userId, $teamId));
    }
}
