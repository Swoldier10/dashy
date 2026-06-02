<?php

namespace Tests\Unit\Domains\Notifications\Enums;

use App\Domains\Notifications\Enums\NotificationCategory;
use App\Domains\Notifications\Enums\NotificationType;
use Tests\TestCase;

class NotificationTypeTest extends TestCase
{
    public function test_default_channels_match_the_catalog(): void
    {
        $expected = [
            'task_assigned' => ['email' => true, 'app' => true],
            'task_unassigned' => ['email' => false, 'app' => true],
            'task_status_changed' => ['email' => false, 'app' => true],
            'task_completed' => ['email' => false, 'app' => true],
            'task_due_soon' => ['email' => true, 'app' => true],
            'task_overdue' => ['email' => true, 'app' => true],
            'task_due_date_changed' => ['email' => false, 'app' => true],
            'task_priority_changed' => ['email' => false, 'app' => true],
            'task_attachment_added' => ['email' => false, 'app' => true],
            'task_created_in_project' => ['email' => false, 'app' => false],
            'invitation_accepted' => ['email' => true, 'app' => true],
            'member_joined' => ['email' => false, 'app' => true],
            'removed_from_team' => ['email' => true, 'app' => true],
            'event_starting_soon' => ['email' => false, 'app' => true],
        ];

        foreach (NotificationType::cases() as $type) {
            $this->assertSame(
                $expected[$type->value],
                $type->defaultChannels(),
                "Unexpected default channels for {$type->value}.",
            );
        }

        $this->assertCount(count($expected), NotificationType::cases());
    }

    public function test_every_type_has_label_description_and_icon(): void
    {
        foreach (NotificationType::cases() as $type) {
            $this->assertNotSame('', $type->label());
            $this->assertNotSame('', $type->description());
            $this->assertNotSame('', $type->icon());
        }
    }

    public function test_categories_cover_tasks_teams_and_calendar(): void
    {
        $byCategory = [];

        foreach (NotificationType::cases() as $type) {
            $byCategory[$type->category()->value][] = $type->value;
        }

        $this->assertCount(10, $byCategory[NotificationCategory::Tasks->value]);
        $this->assertCount(3, $byCategory[NotificationCategory::Teams->value]);
        $this->assertSame(['event_starting_soon'], $byCategory[NotificationCategory::Calendar->value]);
    }

    public function test_only_scheduler_types_are_reminders(): void
    {
        $reminders = array_values(array_map(
            fn (NotificationType $type) => $type->value,
            array_filter(NotificationType::cases(), fn (NotificationType $type) => $type->isReminder()),
        ));

        $this->assertSame(['task_due_soon', 'task_overdue', 'event_starting_soon'], $reminders);
    }

    public function test_subject_kind_follows_category(): void
    {
        $this->assertSame('task', NotificationType::TaskAssigned->subjectKind());
        $this->assertSame('team', NotificationType::MemberJoined->subjectKind());
        $this->assertSame('event', NotificationType::EventStartingSoon->subjectKind());
    }
}
