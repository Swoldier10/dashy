<?php

namespace App\Domains\Notifications\Enums;

enum NotificationType: string
{
    case TaskAssigned = 'task_assigned';
    case TaskUnassigned = 'task_unassigned';
    case TaskStatusChanged = 'task_status_changed';
    case TaskCompleted = 'task_completed';
    case TaskDueSoon = 'task_due_soon';
    case TaskOverdue = 'task_overdue';
    case TaskDueDateChanged = 'task_due_date_changed';
    case TaskPriorityChanged = 'task_priority_changed';
    case TaskAttachmentAdded = 'task_attachment_added';
    case TaskCreatedInProject = 'task_created_in_project';
    case InvitationAccepted = 'invitation_accepted';
    case MemberJoined = 'member_joined';
    case RemovedFromTeam = 'removed_from_team';
    case EventStartingSoon = 'event_starting_soon';

    public function label(): string
    {
        return match ($this) {
            self::TaskAssigned => __('Task assigned to me'),
            self::TaskUnassigned => __('Removed from a task'),
            self::TaskStatusChanged => __('Task status changed'),
            self::TaskCompleted => __('Task completed'),
            self::TaskDueSoon => __('Task due soon'),
            self::TaskOverdue => __('Task overdue'),
            self::TaskDueDateChanged => __('Due date changed'),
            self::TaskPriorityChanged => __('Priority changed'),
            self::TaskAttachmentAdded => __('Attachment added'),
            self::TaskCreatedInProject => __('New task in my projects'),
            self::InvitationAccepted => __('Invitation accepted'),
            self::MemberJoined => __('Member joined my team'),
            self::RemovedFromTeam => __('Removed from a team'),
            self::EventStartingSoon => __('Event starting soon'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TaskAssigned => __('Someone assigns a task to you.'),
            self::TaskUnassigned => __('Someone removes you from a task.'),
            self::TaskStatusChanged => __('A task you are assigned to moves to another status.'),
            self::TaskCompleted => __('A task you are assigned to is completed.'),
            self::TaskDueSoon => __('A task you are assigned to is due within 24 hours.'),
            self::TaskOverdue => __('A task you are assigned to passes its due date.'),
            self::TaskDueDateChanged => __('The due date of a task you are assigned to changes.'),
            self::TaskPriorityChanged => __('The priority of a task you are assigned to changes.'),
            self::TaskAttachmentAdded => __('Someone attaches a file to a task you are assigned to.'),
            self::TaskCreatedInProject => __('A new task is created in one of your team\'s projects.'),
            self::InvitationAccepted => __('Someone accepts your team invitation.'),
            self::MemberJoined => __('A new member joins one of your teams.'),
            self::RemovedFromTeam => __('You are removed from a team.'),
            self::EventStartingSoon => __('A calendar event of yours starts within 30 minutes.'),
        };
    }

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::TaskAssigned,
            self::TaskUnassigned,
            self::TaskStatusChanged,
            self::TaskCompleted,
            self::TaskDueSoon,
            self::TaskOverdue,
            self::TaskDueDateChanged,
            self::TaskPriorityChanged,
            self::TaskAttachmentAdded,
            self::TaskCreatedInProject => NotificationCategory::Tasks,
            self::InvitationAccepted,
            self::MemberJoined,
            self::RemovedFromTeam => NotificationCategory::Teams,
            self::EventStartingSoon => NotificationCategory::Calendar,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TaskAssigned => 'user-plus',
            self::TaskUnassigned => 'minus-circle',
            self::TaskStatusChanged => 'arrows-right-left',
            self::TaskCompleted => 'check-circle',
            self::TaskDueSoon => 'clock',
            self::TaskOverdue => 'exclamation-triangle',
            self::TaskDueDateChanged => 'calendar-days',
            self::TaskPriorityChanged => 'flag',
            self::TaskAttachmentAdded => 'paper-clip',
            self::TaskCreatedInProject => 'plus-circle',
            self::InvitationAccepted => 'envelope-open',
            self::MemberJoined => 'user-group',
            self::RemovedFromTeam => 'user-minus',
            self::EventStartingSoon => 'bell-alert',
        };
    }

    /**
     * @return array{email: bool, app: bool}
     */
    public function defaultChannels(): array
    {
        return match ($this) {
            self::TaskAssigned,
            self::TaskDueSoon,
            self::TaskOverdue,
            self::InvitationAccepted,
            self::RemovedFromTeam => ['email' => true, 'app' => true],
            self::TaskCreatedInProject => ['email' => false, 'app' => false],
            default => ['email' => false, 'app' => true],
        };
    }

    public function isReminder(): bool
    {
        return match ($this) {
            self::TaskDueSoon, self::TaskOverdue, self::EventStartingSoon => true,
            default => false,
        };
    }

    public function subjectKind(): ?string
    {
        return match ($this->category()) {
            NotificationCategory::Tasks => 'task',
            NotificationCategory::Teams => 'team',
            NotificationCategory::Calendar => 'event',
        };
    }
}
