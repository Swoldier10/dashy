<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Tasks\Events\TaskAssigned;
use App\Domains\Tasks\Events\TaskUnassigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskAssignmentNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private NotifyUserService $notify,
    ) {}

    public function handle(TaskAssigned|TaskUnassigned $event): void
    {
        [$type, $recipientUserId] = $event instanceof TaskAssigned
            ? [NotificationType::TaskAssigned, $event->assigneeUserId]
            : [NotificationType::TaskUnassigned, $event->removedUserId];

        $this->notify->execute(new NotificationPayload(
            type: $type,
            recipientUserId: $recipientUserId,
            actorUserId: $event->actorUserId,
            teamId: $event->task->teamId,
            subjectType: 'task',
            subjectId: $event->task->taskId,
            data: $event->baseData(),
        ));
    }
}
