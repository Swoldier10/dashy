<?php

namespace App\Domains\Notifications\Listeners;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Tasks\Events\TaskAttachmentAdded;
use App\Domains\Tasks\Events\TaskDueDateChanged;
use App\Domains\Tasks\Events\TaskPriorityChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskFieldChangeNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private NotifyUserService $notify,
    ) {}

    public function handle(TaskDueDateChanged|TaskPriorityChanged|TaskAttachmentAdded $event): void
    {
        [$type, $extra] = match (true) {
            $event instanceof TaskDueDateChanged => [NotificationType::TaskDueDateChanged, [
                'old_end_date' => $event->oldEndDate,
                'new_end_date' => $event->newEndDate,
            ]],
            $event instanceof TaskPriorityChanged => [NotificationType::TaskPriorityChanged, [
                'old_priority' => $event->oldPriority,
                'new_priority' => $event->newPriority,
            ]],
            $event instanceof TaskAttachmentAdded => [NotificationType::TaskAttachmentAdded, [
                'attached_count' => $event->attachedCount,
            ]],
        };

        $data = array_merge($event->baseData(), $extra);

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
