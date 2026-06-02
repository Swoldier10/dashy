<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Tasks\Events\TaskStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskStatusNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private NotifyUserService $notify,
    ) {}

    public function handle(TaskStatusChanged $event): void
    {
        $type = $event->becameDone()
            ? NotificationType::TaskCompleted
            : NotificationType::TaskStatusChanged;

        $data = array_merge($event->baseData(), [
            'old_status_name' => $event->oldStatusName,
            'new_status_name' => $event->newStatusName,
        ]);

        foreach ($event->assigneeUserIds as $assigneeUserId) {
            $this->notify->execute(new NotificationPayload(
                type: $type,
                recipientUserId: $assigneeUserId,
                actorUserId: $event->actorUserId,
                teamId: $event->task->teamId,
                subjectType: 'task',
                subjectId: $event->task->taskId,
                data: $data,
            ));
        }
    }
}
