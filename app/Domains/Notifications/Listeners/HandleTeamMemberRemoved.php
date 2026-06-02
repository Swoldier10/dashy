<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\DeleteTeamNotificationsForUserService;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Teams\Events\TeamMemberRemoved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * One listener with explicit ordering: first purge the removed member's
 * notifications for the team, then create the removal notice — so the purge
 * can never race away the very notification that explains the removal.
 */
final class HandleTeamMemberRemoved implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private DeleteTeamNotificationsForUserService $deleteTeamNotifications,
        private NotifyUserService $notify,
    ) {}

    public function handle(TeamMemberRemoved $event): void
    {
        $this->deleteTeamNotifications->execute($event->removedUserId, $event->teamId);

        if ($event->wasSelfLeave) {
            return;
        }

        $this->notify->execute(new NotificationPayload(
            type: NotificationType::RemovedFromTeam,
            recipientUserId: $event->removedUserId,
            actorUserId: $event->actorUserId,
            teamId: $event->teamId,
            subjectType: 'team',
            subjectId: $event->teamId,
            data: array_merge($event->baseData(), ['actor_name' => $event->actorName]),
        ));
    }
}
