<?php

namespace App\Domains\Teams\Services;

use App\Domains\Tasks\Services\RemoveMemberAssignmentsForTeamService;
use App\Domains\Teams\Actions\CountTeamOwnersAction;
use App\Domains\Teams\Actions\DetachTeamMemberAction;
use App\Domains\Teams\Actions\IsTeamMemberAction;
use App\Domains\Teams\Actions\IsTeamOwnerAction;
use App\Domains\Teams\Events\TeamMemberRemoved;
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
        private IsTeamMemberAction $isMember,
        private IsTeamOwnerAction $isOwner,
        private CountTeamOwnersAction $countOwners,
        private RemoveMemberAssignmentsForTeamService $removeAssignments,
    ) {}

    public function execute(User $actor, Team $team, User $target): void
    {
        $isSelfLeave = $actor->is($target);

        if (! $isSelfLeave) {
            Gate::forUser($actor)->authorize('removeMember', $team);
        } else {
            // Self-leave: actor must already be a member of the team.
            if (! $this->isMember->execute($team, (int) $actor->id)) {
                throw new AuthorizationException(__('You are not a member of this team.'));
            }
        }

        if ($team->personal_team && $isSelfLeave) {
            throw ValidationException::withMessages([
                'team' => __('You can\'t leave your personal team.'),
            ]);
        }

        if ($this->isOwner->execute($team, (int) $target->id)) {
            if ($this->countOwners->execute($team) <= 1) {
                throw ValidationException::withMessages([
                    'team' => __('This team must have at least one owner.'),
                ]);
            }
        }

        DB::transaction(function () use ($team, $target, $actor) {
            // Clear the leaving member's task assignments so they don't linger
            // as a ghost assignee (and so they can be cleanly re-added later).
            // Time entries are intentionally untouched — they are retained
            // work/billing records, removed only by manual deletion or by
            // deleting the task.
            $this->removeAssignments->execute((int) $team->id, (int) $target->id);

            $this->detachMember->execute($team, $target);

            DB::afterCommit(fn () => event(TeamMemberRemoved::fromTeam($team, $target, $actor)));
        });
    }
}
