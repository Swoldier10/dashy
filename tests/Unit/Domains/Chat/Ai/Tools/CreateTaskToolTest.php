<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\CreateTaskTool;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskToolTest extends TestCase
{
    use RefreshDatabase;

    private function setupAccessibleProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $notStarted = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'Backlog',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'Doing',
            'position' => 1,
        ]);

        return [$user, $project, $notStarted];
    }

    public function test_validate_rejects_unknown_project(): void
    {
        $user = User::factory()->create();

        $result = app(CreateTaskTool::class)->validate($user, ['project_id' => 99999, 'name' => 'X']);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }

    public function test_validate_falls_back_to_default_status_when_id_is_outside_project(): void
    {
        [$user, $project, $notStarted] = $this->setupAccessibleProject();
        $foreign = ProjectStatus::factory()->create();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'status_id' => $foreign->id,
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame($notStarted->id, $result->normalized['status_id']);
    }

    public function test_validate_rejects_assignee_not_in_team(): void
    {
        [$user, $project] = $this->setupAccessibleProject();
        $stranger = User::factory()->create();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'assignee_user_ids' => [$stranger->id],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_invalid_priority(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'priority' => 'critical',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_drops_end_date_before_start_instead_of_failing(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-01',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('2026-05-10', $result->normalized['start_date']);
        $this->assertSame('2026-05-17', $result->normalized['end_date']);
    }

    public function test_validate_drops_implausible_far_future_dates(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'end_date' => '4096-05-10',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame(now()->addDays(7)->toDateString(), $result->normalized['end_date']);
    }

    public function test_validate_defaults_end_date_to_start_plus_seven_days_when_missing(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'A',
            'start_date' => '2026-05-15',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('2026-05-15', $result->normalized['start_date']);
        $this->assertSame('2026-05-22', $result->normalized['end_date']);
    }

    public function test_validate_happy_path_normalises_defaults(): void
    {
        [$user, $project, $notStarted] = $this->setupAccessibleProject();

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => '  Fix login ',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('Fix login', $result->normalized['name']);
        $this->assertSame($notStarted->id, $result->normalized['status_id']);
        $this->assertSame('normal', $result->normalized['priority']);
        $this->assertSame(now()->toDateString(), $result->normalized['start_date']);
        $this->assertSame(now()->addDays(7)->toDateString(), $result->normalized['end_date']);
        $this->assertSame([$user->id], $result->normalized['assignee_user_ids']);
    }

    public function test_validate_preserves_existing_image_attachments_during_revalidation(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        // Create a chat with a NEW image — different from the snapshot in args.
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'new image',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/new.png',
                'url' => 'https://test/new.png',
                'mime' => 'image/png',
                'name' => 'new.png',
            ]],
        ]);

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'T',
            'image_attachments' => [[
                'path' => 'chat-attachments/x/original.png',
                'url' => 'https://test/original.png',
                'mime' => 'image/png',
                'name' => 'original.png',
            ]],
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame(
            'chat-attachments/x/original.png',
            $result->normalized['image_attachments'][0]['path'],
        );
    }

    public function test_validate_snapshots_image_from_earlier_user_message_when_latest_has_none(): void
    {
        [$user, $project] = $this->setupAccessibleProject();

        // The image arrives first, THEN a text-only disambiguation reply ("which
        // project?" → "Folienzuschnitt"). The create_task call fires after the
        // text-only reply — the image must still be snapshotted.
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'create a task based on this',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/screenshot.png',
                'url' => 'https://test/screenshot.png',
                'mime' => 'image/png',
                'name' => 'screenshot.png',
            ]],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => 'Which project?',
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'Folienzuschnitt',
        ]);

        $result = app(CreateTaskTool::class)->validate($user, [
            'project_id' => $project->id,
            'name' => 'T',
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertCount(1, $result->normalized['image_attachments']);
        $this->assertSame(
            'chat-attachments/x/screenshot.png',
            $result->normalized['image_attachments'][0]['path'],
        );
    }

    public function test_execute_persists_task_with_assignee(): void
    {
        [$user, $project, $notStarted] = $this->setupAccessibleProject();

        $tool = app(CreateTaskTool::class);
        $valid = $tool->validate($user, [
            'project_id' => $project->id,
            'name' => 'Do the thing',
        ]);
        $this->assertTrue($valid->valid);

        $result = $tool->execute($user, $valid->normalized);

        $task = Task::findOrFail($result['task_id']);
        $this->assertSame('Do the thing', $task->name);
        $this->assertSame($notStarted->id, $task->project_status_id);
        $this->assertSame([$user->id], $task->assignees()->pluck('users.id')->all());
    }
}
