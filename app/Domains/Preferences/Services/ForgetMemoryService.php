<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\DeleteTeamPreferenceAction;
use App\Domains\Preferences\Actions\DeleteUserPreferenceAction;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Deletes a stored preference by key. Authorises by scope: user memories
 * require the actor to own the row; team memories require team membership.
 */
final class ForgetMemoryService
{
    public function __construct(
        private DeleteUserPreferenceAction $deleteUser,
        private DeleteTeamPreferenceAction $deleteTeam,
        private FindTeamForUserService $findTeamForUser,
    ) {}

    public function execute(User $actor, string $scope, string $key, ?int $teamId = null): bool
    {
        $key = trim($key);
        if ($key === '') {
            throw ValidationException::withMessages([
                'key' => __('A preference key is required.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $scope, $key, $teamId): bool {
            if ($scope === 'user') {
                return $this->deleteUser->execute($actor->id, $key) > 0;
            }

            if ($scope === 'team') {
                if ($teamId === null) {
                    throw ValidationException::withMessages([
                        'team_id' => __('A team_id is required for team-scoped memories.'),
                    ]);
                }
                $team = $this->findTeamForUser->execute($actor, $teamId);
                if ($team === null) {
                    throw new ModelNotFoundException('Team not found or not accessible.');
                }

                return $this->deleteTeam->execute($team->id, $key) > 0;
            }

            throw ValidationException::withMessages([
                'scope' => __('scope must be "user" or "team".'),
            ]);
        });
    }
}
