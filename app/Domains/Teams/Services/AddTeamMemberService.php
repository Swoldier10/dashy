<?php

namespace App\Domains\Teams\Services;

use App\Domains\Auth\Services\LookupUserByEmailService;
use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Actions\IsTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Events\TeamMemberJoined;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use App\Support\Concerns\DetectsUniqueConstraintViolations;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AddTeamMemberService
{
    use DetectsUniqueConstraintViolations;

    public function __construct(
        private LookupUserByEmailService $findUserByEmail,
        private AttachTeamMemberAction $attachMember,
        private IsTeamMemberAction $isMember,
        private ListTeamMemberIdsService $listTeamMemberIds,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Team $team, array $input): User
    {
        Gate::forUser($actor)->authorize('addMember', $team);

        if ($team->personal_team) {
            throw ValidationException::withMessages([
                'email' => __("You can't add members to your personal team."),
            ]);
        }

        $validated = Validator::make($input, [
            'email' => ['required', 'email'],
        ])->validate();

        $email = $validated['email'];
        $target = $this->findUserByEmail->execute($email);

        if ($target === null) {
            throw ValidationException::withMessages([
                'email' => __('No Dashy account with that email.'),
            ]);
        }

        if ($target->is($actor)) {
            throw ValidationException::withMessages([
                'email' => __('You\'re already a member of this team.'),
            ]);
        }

        if ($this->isMember->execute($team, (int) $target->id)) {
            throw ValidationException::withMessages([
                'email' => __('That user is already a member.'),
            ]);
        }

        return DB::transaction(function () use ($team, $target) {
            try {
                $this->attachMember->execute($team, $target, TeamRole::Member);
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    throw ValidationException::withMessages([
                        'email' => __('That user is already a member.'),
                    ]);
                }

                throw $e;
            }

            $otherMemberIds = array_values(array_diff($this->listTeamMemberIds->execute($team), [$target->id]));

            DB::afterCommit(fn () => event(TeamMemberJoined::fromTeam($team, $target, $otherMemberIds)));

            return $target;
        });
    }
}
