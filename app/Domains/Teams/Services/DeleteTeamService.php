<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\DeleteTeamAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class DeleteTeamService
{
    public function __construct(
        private DeleteTeamAction $deleteTeam,
    ) {}

    public function execute(User $actor, Team $team): void
    {
        Gate::forUser($actor)->authorize('delete', $team);

        if ($team->personal_team) {
            throw ValidationException::withMessages([
                'team' => __('You can\'t delete your personal team.'),
            ]);
        }

        DB::transaction(fn () => $this->deleteTeam->execute($team));
    }
}
