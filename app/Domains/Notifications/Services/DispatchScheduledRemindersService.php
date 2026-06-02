<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Calendar\Services\ListEventsStartingWithinService;
use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListTasksByDueStateService;
use Carbon\CarbonImmutable;

/**
 * Scheduler orchestration for time-based reminders: task due-soon, task
 * overdue, and event starting-soon. Reads candidates through the owning
 * domains' public services; every payload carries a dedupe key embedding
 * the due/start timestamp, so overlapping scheduler ticks are idempotent
 * and a moved date re-arms the reminder.
 */
final class DispatchScheduledRemindersService
{
    public const EVENT_WINDOW_MINUTES = 30;

    public function __construct(
        private ListTasksByDueStateService $listTasksByDueState,
        private ListEventsStartingWithinService $listEventsStartingWithin,
        private NotifyUserService $notify,
    ) {}

    /**
     * Returns the number of reminder deliveries attempted (before
     * preference gating and dedupe).
     */
    public function execute(): int
    {
        $now = CarbonImmutable::now();
        $attempted = 0;

        foreach ($this->listTasksByDueState->dueSoon($now) as $task) {
            $attempted += $this->notifyTaskAssignees($task, NotificationType::TaskDueSoon);
        }

        foreach ($this->listTasksByDueState->overdue($now) as $task) {
            $attempted += $this->notifyTaskAssignees($task, NotificationType::TaskOverdue);
        }

        $occurrences = $this->listEventsStartingWithin->execute($now, $now->addMinutes(self::EVENT_WINDOW_MINUTES));

        foreach ($occurrences as $occurrence) {
            $event = $occurrence->event;

            $this->notify->execute(new NotificationPayload(
                type: NotificationType::EventStartingSoon,
                recipientUserId: (int) $event->user_id,
                subjectType: 'event',
                subjectId: (int) $event->id,
                data: [
                    'event_id' => (int) $event->id,
                    'event_title' => (string) $event->title,
                    'starts_at' => $occurrence->startAt->toIso8601String(),
                ],
                dedupeKey: 'event_starting_soon:'.$occurrence->key(),
            ));
            $attempted++;
        }

        return $attempted;
    }

    private function notifyTaskAssignees(Task $task, NotificationType $type): int
    {
        $data = [
            'task_id' => $task->id,
            'task_name' => (string) $task->name,
            'project_id' => (int) $task->project_id,
            'project_name' => (string) ($task->project?->name ?? ''),
            'end_date' => $task->end_date?->toIso8601String(),
        ];

        $endTimestamp = $task->end_date?->getTimestamp() ?? 0;
        $teamId = (int) ($task->project?->team_id ?? 0);
        $attempted = 0;

        foreach ($task->assignees as $assignee) {
            $this->notify->execute(new NotificationPayload(
                type: $type,
                recipientUserId: (int) $assignee->id,
                teamId: $teamId > 0 ? $teamId : null,
                subjectType: 'task',
                subjectId: $task->id,
                data: $data,
                dedupeKey: "{$type->value}:{$task->id}:{$assignee->id}:{$endTimestamp}",
            ));
            $attempted++;
        }

        return $attempted;
    }
}
