<?php

namespace App\Domains\Teams\Services;

use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AddTeamMemberService
{
    public function __construct(
        private FindUserByEmailAction $findUserByEmail,
        private AttachTeamMemberAction $attachMember,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Team $team, array $input): User
    {
        Gate::forUser($actor)->authorize('addMember', $team);

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

        if ($team->members()->whereKey($target->id)->exists()) {
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

            return $target;
        });
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return ($e->getCode() === '23000') || ($e->errorInfo[0] ?? null) === '23000';
    }
}
