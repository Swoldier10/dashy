<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Teams\Events\TeamInvitationAccepted;
use App\Domains\Teams\Events\TeamMemberJoined;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTeamMembershipNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private NotifyUserService $notify,
    ) {}

    public function handle(TeamInvitationAccepted|TeamMemberJoined $event): void
    {
        if ($event instanceof TeamInvitationAccepted) {
            if ($event->invitedByUserId === null) {
                return;
            }

            $this->notify->execute(new NotificationPayload(
                type: NotificationType::InvitationAccepted,
                recipientUserId: $event->invitedByUserId,
                actorUserId: $event->acceptedByUserId,
                teamId: $event->teamId,
                subjectType: 'team',
                subjectId: $event->teamId,
                data: array_merge($event->baseData(), ['member_name' => $event->acceptedByName]),
            ));

            return;
        }

        // The inviter gets the richer invitation_accepted notice instead.
        $recipients = array_diff($event->otherMemberIds, array_filter([
            $event->joinedUserId,
            $event->invitedByUserId,
        ]));

        foreach ($recipients as $memberId) {
            $this->notify->execute(new NotificationPayload(
                type: NotificationType::MemberJoined,
                recipientUserId: $memberId,
                actorUserId: $event->joinedUserId,
                teamId: $event->teamId,
                subjectType: 'team',
                subjectId: $event->teamId,
                data: array_merge($event->baseData(), ['member_name' => $event->joinedUserName]),
            ));
        }
    }
}
