<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Models\Notification;

class DeleteNotificationsForUserInTeamAction
{
    /**
     * Removes a user's notifications scoped to one team (used when they are
     * removed from it). The removed_from_team notice itself is preserved so
     * the purge can never erase the very notification explaining the removal.
     */
    public function execute(int $userId, int $teamId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('type', '!=', NotificationType::RemovedFromTeam->value)
            ->delete();
    }
}
