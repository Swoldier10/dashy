<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\DetachTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class RemoveTeamMemberService
{
    public function __construct(
        private DetachTeamMemberAction $detachMember,
    ) {}

    public function execute(User $actor, Team $team, User $target): void
    {
        $isSelfLeave = $actor->is($target);

        if (! $isSelfLeave) {
            Gate::forUser($actor)->authorize('removeMember', $team);
        } else {
            // Self-leave: actor must already be a member of the team.
            if (! $team->members()->whereKey($actor->id)->exists()) {
                throw new AuthorizationException(__('You are not a member of this team.'));
            }
        }

        if ($team->personal_team && $isSelfLeave) {
            throw ValidationException::withMessages([
                'team' => __('You can\'t leave your personal team.'),
            ]);
        }

        $targetIsOwner = $team->members()
            ->whereKey($target->id)
            ->wherePivot('role', TeamRole::Owner->value)
            ->exists();

        if ($targetIsOwner) {
            $ownerCount = $team->members()
                ->wherePivot('role', TeamRole::Owner->value)
                ->count();

            if ($ownerCount <= 1) {
                throw ValidationException::withMessages([
                    'team' => __('This team must have at least one owner.'),
                ]);
            }
        }

        DB::transaction(fn () => $this->detachMember->execute($team, $target));
    }
}
