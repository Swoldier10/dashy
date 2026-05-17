<?php

namespace App\Domains\Auth\Services;

use App\Concerns\PasswordValidationRules;
use App\Domains\Auth\Actions\DeleteUserAction;
use App\Domains\Teams\Actions\DeleteOrphanedTeamsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class DeleteAccountService
{
    use PasswordValidationRules;

    public function __construct(
        private DeleteUserAction $deleteUser,
        private DeleteOrphanedTeamsForUserAction $deleteOrphanedTeams,
    ) {}

    /**
     * Validate the supplied confirmation against the user.
     *
     * Split from delete() so the caller can run a session log-out between the
     * two steps. Auth::logout() cycles the user's remember_token via save(),
     * which would re-insert a row deleted in the same request.
     *
     * @param  array<string, mixed>  $input
     */
    public function validateInputs(User $user, array $input): void
    {
        $this->guardAgainstSoleOwnershipOfSharedTeams($user);

        if ($user->password !== null) {
            Validator::make($input, [
                'password' => $this->currentPasswordRules(),
            ])->validate();

            return;
        }

        Validator::make($input, [
            'confirmation' => ['required', 'in:DELETE'],
        ], [
            'confirmation.in' => __('Type DELETE to confirm.'),
        ])->validate();
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->guardAgainstSoleOwnershipOfSharedTeams($user);
            $this->deleteOrphanedTeams->execute($user);
            $this->deleteUser->execute($user);
        });
    }

    private function guardAgainstSoleOwnershipOfSharedTeams(User $user): void
    {
        $blockingTeams = $user->teams()
            ->wherePivot('role', TeamRole::Owner->value)
            ->withCount('members')
            ->get()
            ->filter(function ($team) use ($user) {
                if ($team->members_count <= 1) {
                    return false;
                }

                $ownerIds = $team->members()
                    ->wherePivot('role', TeamRole::Owner->value)
                    ->pluck('users.id')
                    ->all();

                return count($ownerIds) === 1 && (int) $ownerIds[0] === $user->id;
            });

        if ($blockingTeams->isNotEmpty()) {
            throw ValidationException::withMessages([
                'team' => __('Transfer or delete these teams first: :teams', [
                    'teams' => $blockingTeams->pluck('name')->join(', '),
                ]),
            ]);
        }
    }
}
