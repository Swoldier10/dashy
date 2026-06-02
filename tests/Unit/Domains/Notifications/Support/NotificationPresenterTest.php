<?php

namespace Tests\Unit\Domains\Notifications\Support;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Support\NotificationPresenter;
use Tests\TestCase;

class NotificationPresenterTest extends TestCase
{
    private NotificationPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new NotificationPresenter;
    }

    public function test_title_interpolates_actor_and_task(): void
    {
        $title = $this->presenter->title(NotificationType::TaskAssigned, [
            'actor_name' => 'Anna',
            'task_name' => 'Fix the build',
        ]);

        $this->assertSame('Anna assigned you to "Fix the build"', $title);
    }

    public function test_attachment_title_pluralizes(): void
    {
        $data = ['actor_name' => 'Anna', 'task_name' => 'Fix', 'attached_count' => 1];
        $this->assertSame(
            'Anna added an attachment to "Fix"',
            $this->presenter->title(NotificationType::TaskAttachmentAdded, $data),
        );

        $data['attached_count'] = 3;
        $this->assertSame(
            'Anna added 3 attachments to "Fix"',
            $this->presenter->title(NotificationType::TaskAttachmentAdded, $data),
        );
    }

    public function test_title_falls_back_when_snapshot_fields_are_missing(): void
    {
        $title = $this->presenter->title(NotificationType::TaskCompleted, []);

        $this->assertSame('Someone completed "a task"', $title);
    }

    public function test_body_combines_project_and_due_line(): void
    {
        $body = $this->presenter->body(NotificationType::TaskDueSoon, [
            'project_name' => 'Website',
            'end_date' => '2026-06-03T15:00:00+00:00',
        ]);

        $this->assertSame('Website · Due Jun 3, 15:00', $body);
    }

    public function test_due_date_change_body_handles_removed_date(): void
    {
        $body = $this->presenter->body(NotificationType::TaskDueDateChanged, [
            'project_name' => 'Website',
            'new_end_date' => null,
        ]);

        $this->assertSame('Website · Due date removed', $body);
    }

    public function test_cta_url_per_subject_kind(): void
    {
        $this->assertSame(
            route('tasks').'?task=42',
            $this->presenter->ctaUrl(NotificationType::TaskAssigned, ['task_id' => 42]),
        );
        $this->assertSame(
            route('teams.show', 7),
            $this->presenter->ctaUrl(NotificationType::MemberJoined, ['team_id' => 7]),
        );
        $this->assertSame(
            route('calendar'),
            $this->presenter->ctaUrl(NotificationType::EventStartingSoon, []),
        );
    }

    public function test_cta_url_is_null_when_subject_id_is_missing(): void
    {
        $this->assertNull($this->presenter->ctaUrl(NotificationType::TaskAssigned, []));
        $this->assertNull($this->presenter->ctaUrl(NotificationType::MemberJoined, []));
    }

    public function test_removed_from_team_links_to_teams_index_not_the_team(): void
    {
        $this->assertSame(
            route('teams.index'),
            $this->presenter->ctaUrl(NotificationType::RemovedFromTeam, ['team_id' => 7]),
        );
        $this->assertSame('View teams', $this->presenter->ctaLabel(NotificationType::RemovedFromTeam));
    }
}
