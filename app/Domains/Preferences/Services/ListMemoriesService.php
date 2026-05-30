<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\ListTeamPreferencesAction;
use App\Domains\Preferences\Actions\ListUserPreferencesAction;
use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Returns the actor's stored memories at the given scope. Filtered to keys
 * beginning with "memory." so structured prefs (e.g. default-project) stay
 * out of the chat-facing list.
 */
final class ListMemoriesService
{
    public function __construct(
        private ListUserPreferencesAction $listUser,
        private ListTeamPreferencesAction $listTeam,
        private FindTeamForUserService $findTeamForUser,
    ) {}

    /**
     * @return Collection<int, UserPreference|TeamPreference>
     */
    public function execute(User $actor, string $scope, ?int $teamId = null): Collection
    {
        if ($scope === 'user') {
            return $this->listUser->execute($actor->id, 'memory.');
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

            return $this->listTeam->execute($team->id, 'memory.');
        }

        throw ValidationException::withMessages([
            'scope' => __('scope must be "user" or "team".'),
        ]);
    }
}
