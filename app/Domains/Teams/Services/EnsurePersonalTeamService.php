<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Actions\CreateTeamAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class EnsurePersonalTeamService
{
    public function __construct(
        private CreateTeamAction $createTeam,
        private AttachTeamMemberAction $attachMember,
    ) {}

    /**
     * Idempotent: returns the user's personal team, creating one if
     * they don't have one yet. Safe to call repeatedly.
     */
    public function execute(User $user): Team
    {
        return DB::transaction(function () use ($user) {
            $existing = $user->teams()
                ->where('teams.personal_team', true)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $team = $this->createTeam->execute([
                'name' => $this->personalTeamName($user),
                'personal_team' => true,
            ]);

            $this->attachMember->execute($team, $user, TeamRole::Owner);

            return $team;
        });
    }

    private function personalTeamName(User $user): string
    {
        $first = trim((string) ($user->first_name ?? ''));
        if ($first !== '') {
            return $first."'s Team";
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name."'s Team";
        }

        return 'Personal';
    }
}
