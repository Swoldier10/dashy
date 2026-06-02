<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Tasks\Events\TaskCreatedInProject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskCreatedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private NotifyUserService $notify,
    ) {}

    public function handle(TaskCreatedInProject $event): void
    {
        // Initial assignees already receive task_assigned for this creation.
        $recipients = array_diff($event->teamMemberIds, $event->assigneeUserIds);

        foreach ($recipients as $memberId) {
            $this->notify->execute(new NotificationPayload(
                type: NotificationType::TaskCreatedInProject,
                recipientUserId: $memberId,
                actorUserId: $event->actorUserId,
                teamId: $event->task->teamId,
                subjectType: 'task',
                subjectId: $event->task->taskId,
                data: $event->baseData(),
            ));
        }
    }
}
