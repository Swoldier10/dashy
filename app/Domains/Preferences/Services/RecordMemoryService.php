<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\UpsertTeamPreferenceAction;
use App\Domains\Preferences\Actions\UpsertUserPreferenceAction;
use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Persists a free-form "memory" fact at either user or team scope. The key
 * is auto-generated as `memory.<slug>` so multiple memories can coexist
 * without the AI having to invent names. User-scoped memories belong to the
 * actor; team-scoped memories require team membership.
 */
final class RecordMemoryService
{
    private const MAX_FACT_LENGTH = 2000;

    public function __construct(
        private UpsertUserPreferenceAction $upsertUser,
        private UpsertTeamPreferenceAction $upsertTeam,
        private FindTeamForUserService $findTeamForUser,
    ) {}

    /**
     * @return UserPreference|TeamPreference
     */
    public function execute(User $actor, string $scope, string $fact, ?int $teamId = null)
    {
        $fact = trim($fact);
        if ($fact === '') {
            throw ValidationException::withMessages([
                'fact' => __('The memory cannot be empty.'),
            ]);
        }
        if (mb_strlen($fact) > self::MAX_FACT_LENGTH) {
            throw ValidationException::withMessages([
                'fact' => __('The memory must be :max characters or fewer.', ['max' => self::MAX_FACT_LENGTH]),
            ]);
        }

        $key = 'memory.'.Str::lower(Str::random(8));
        $value = ['fact' => $fact, 'created_at' => now()->toIso8601String()];

        return DB::transaction(function () use ($actor, $scope, $teamId, $key, $value) {
            if ($scope === 'user') {
                return $this->upsertUser->execute($actor->id, $key, $value);
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

                return $this->upsertTeam->execute($team->id, $key, $value);
            }

            throw ValidationException::withMessages([
                'scope' => __('scope must be "user" or "team".'),
            ]);
        });
    }
}
