<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\RemoveMemberAssignmentsForTeamAction;

/**
 * Removes a user's task assignments across a team's projects. Called by the
 * Teams domain when a member is removed, so the cross-domain boundary stays
 * service-to-service (rule 1). The caller owns the transaction and has already
 * authorized the removal, so no policy/transaction is opened here.
 */
final class RemoveMemberAssignmentsForTeamService
{
    public function __construct(
        private RemoveMemberAssignmentsForTeamAction $removeAssignments,
    ) {}

    public function execute(int $teamId, int $userId): int
    {
        return $this->removeAssignments->execute($teamId, $userId);
    }
}
