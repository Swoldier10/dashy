<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Actions\CreateTeamAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class CreateTeamService
{
    public function __construct(
        private CreateTeamAction $createTeam,
        private AttachTeamMemberAction $attachMember,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $creator, array $input): Team
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
        ])->validate();

        return DB::transaction(function () use ($creator, $validated) {
            $team = $this->createTeam->execute([
                'name' => $validated['name'],
                'personal_team' => false,
            ]);

            $this->attachMember->execute($team, $creator, TeamRole::Owner);

            return $team;
        });
    }
}
