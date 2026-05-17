<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class RenameTeamService
{
    public function __construct(
        private UpdateTeamAction $updateTeam,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Team $team, array $input): Team
    {
        Gate::forUser($actor)->authorize('update', $team);

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
        ])->validate();

        return DB::transaction(fn () => $this->updateTeam->execute($team, [
            'name' => $validated['name'],
        ]));
    }
}
