<?php

namespace App\Domains\Notifications\Support;

use App\Domains\Notifications\Enums\NotificationType;
use Carbon\CarbonImmutable;

/**
 * The single source of truth for rendering a notification of a given type:
 * feed rows, e-mails, and AI tool output all read from here.
 */
final class NotificationPresenter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function title(NotificationType $type, array $data): string
    {
        $actor = (string) ($data['actor_name'] ?? __('Someone'));
        $task = (string) ($data['task_name'] ?? __('a task'));

        return match ($type) {
            NotificationType::TaskAssigned => __(':actor assigned you to ":task"', ['actor' => $actor, 'task' => $task]),
            NotificationType::TaskUnassigned => __(':actor removed you from ":task"', ['actor' => $actor, 'task' => $task]),
            NotificationType::TaskStatusChanged => __(':actor moved ":task" to :status', [
                'actor' => $actor,
                'task' => $task,
                'status' => (string) ($data['new_status_name'] ?? __('another status')),
            ]),
            NotificationType::TaskCompleted => __(':actor completed ":task"', ['actor' => $actor, 'task' => $task]),
            NotificationType::TaskDueSoon => __('":task" is due soon', ['task' => $task]),
            NotificationType::TaskOverdue => __('":task" is overdue', ['task' => $task]),
            NotificationType::TaskDueDateChanged => __(':actor changed the due date of ":task"', ['actor' => $actor, 'task' => $task]),
            NotificationType::TaskPriorityChanged => __(':actor set ":task" to :priority priority', [
                'actor' => $actor,
                'task' => $task,
                'priority' => $this->priorityLabel($data),
            ]),
            NotificationType::TaskAttachmentAdded => trans_choice(
                '{1} :actor added an attachment to ":task"|[2,*] :actor added :count attachments to ":task"',
                max(1, (int) ($data['attached_count'] ?? 1)),
                ['actor' => $actor, 'task' => $task, 'count' => (int) ($data['attached_count'] ?? 1)],
            ),
            NotificationType::TaskCreatedInProject => __(':actor created ":task" in :project', [
                'actor' => $actor,
                'task' => $task,
                'project' => (string) ($data['project_name'] ?? __('a project')),
            ]),
            NotificationType::InvitationAccepted => __(':member accepted your invitation to :team', [
                'member' => (string) ($data['member_name'] ?? __('Someone')),
                'team' => $this->teamName($data),
            ]),
            NotificationType::MemberJoined => __(':member joined :team', [
                'member' => (string) ($data['member_name'] ?? __('Someone')),
                'team' => $this->teamName($data),
            ]),
            NotificationType::RemovedFromTeam => __('You were removed from :team', ['team' => $this->teamName($data)]),
            NotificationType::EventStartingSoon => __('":event" starts soon', [
                'event' => (string) ($data['event_title'] ?? __('An event')),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function body(NotificationType $type, array $data): ?string
    {
        return match ($type) {
            NotificationType::TaskDueSoon,
            NotificationType::TaskOverdue => $this->withProject($data, $this->dueLine($data['end_date'] ?? null)),
            NotificationType::TaskDueDateChanged => $this->withProject($data, $this->dueChangeLine($data)),
            NotificationType::EventStartingSoon => $this->startsAtLine($data),
            NotificationType::InvitationAccepted,
            NotificationType::MemberJoined,
            NotificationType::RemovedFromTeam => null,
            default => isset($data['project_name']) ? (string) $data['project_name'] : null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function ctaUrl(NotificationType $type, array $data): ?string
    {
        if ($type === NotificationType::RemovedFromTeam) {
            return route('teams.index');
        }

        return match ($type->subjectKind()) {
            'task' => isset($data['task_id']) ? route('tasks').'?task='.(int) $data['task_id'] : null,
            'team' => isset($data['team_id']) ? route('teams.show', (int) $data['team_id']) : null,
            'event' => route('calendar'),
            default => null,
        };
    }

    public function ctaLabel(NotificationType $type): string
    {
        if ($type === NotificationType::RemovedFromTeam) {
            return __('View teams');
        }

        return match ($type->subjectKind()) {
            'task' => __('View task'),
            'team' => __('View team'),
            'event' => __('Open calendar'),
            default => __('Open Dashy'),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function teamName(array $data): string
    {
        return (string) ($data['team_name'] ?? __('a team'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function priorityLabel(array $data): string
    {
        return ucfirst(str_replace('_', ' ', (string) ($data['new_priority'] ?? __('another'))));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function withProject(array $data, ?string $line): ?string
    {
        $project = isset($data['project_name']) ? (string) $data['project_name'] : null;

        if ($line === null) {
            return $project;
        }

        return $project === null ? $line : $project.' · '.$line;
    }

    private function dueLine(mixed $endDate): ?string
    {
        if (! is_string($endDate) || $endDate === '') {
            return null;
        }

        return __('Due :date', ['date' => CarbonImmutable::parse($endDate)->format('M j, H:i')]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dueChangeLine(array $data): ?string
    {
        $new = $data['new_end_date'] ?? null;

        if (! is_string($new) || $new === '') {
            return __('Due date removed');
        }

        return __('New due date: :date', ['date' => CarbonImmutable::parse($new)->format('M j, H:i')]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function startsAtLine(array $data): ?string
    {
        $startsAt = $data['starts_at'] ?? null;

        if (! is_string($startsAt) || $startsAt === '') {
            return null;
        }

        return __('Starts at :time', ['time' => CarbonImmutable::parse($startsAt)->format('H:i')]);
    }
}
