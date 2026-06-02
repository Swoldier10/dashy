<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\AddTaskAttachmentsService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AddTaskAttachmentsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function memberTask(?array $attachments = null): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'attachments' => $attachments]);

        return [$user, $task];
    }

    private function image(string $path): array
    {
        return ['path' => $path, 'url' => 'https://t/'.$path, 'mime' => 'image/png', 'name' => basename($path)];
    }

    public function test_appends_image_to_a_task_with_no_attachments(): void
    {
        [$user, $task] = $this->memberTask();

        $result = app(AddTaskAttachmentsService::class)->execute($user, $task->id, [$this->image('a/one.png')]);

        $this->assertSame(1, $result['attached_count']);
        $task->refresh();
        $this->assertCount(1, $task->attachments);
        $this->assertSame('image', $task->attachments[0]['type']);
        $this->assertSame('a/one.png', $task->attachments[0]['path']);
    }

    public function test_appends_to_existing_attachments(): void
    {
        [$user, $task] = $this->memberTask([
            ['type' => 'image', 'path' => 'a/existing.png', 'url' => 'https://t/a/existing.png', 'mime' => 'image/png', 'name' => 'existing.png'],
        ]);

        $result = app(AddTaskAttachmentsService::class)->execute($user, $task->id, [$this->image('a/new.png')]);

        $this->assertSame(1, $result['attached_count']);
        $task->refresh();
        $this->assertCount(2, $task->attachments);
        $this->assertSame(['a/existing.png', 'a/new.png'], array_column($task->attachments, 'path'));
    }

    public function test_dedupes_by_path_and_performs_no_write(): void
    {
        [$user, $task] = $this->memberTask([
            ['type' => 'image', 'path' => 'a/dup.png', 'url' => 'https://t/a/dup.png', 'mime' => 'image/png', 'name' => 'dup.png'],
        ]);

        $result = app(AddTaskAttachmentsService::class)->execute($user, $task->id, [$this->image('a/dup.png')]);

        $this->assertSame(0, $result['attached_count']);
        $task->refresh();
        $this->assertCount(1, $task->attachments);
    }

    public function test_empty_incoming_is_a_noop(): void
    {
        [$user, $task] = $this->memberTask();

        $result = app(AddTaskAttachmentsService::class)->execute($user, $task->id, []);

        $this->assertSame(0, $result['attached_count']);
        $task->refresh();
        $this->assertNull($task->attachments);
    }

    public function test_caps_total_attachments_at_ten(): void
    {
        $existing = [];
        for ($i = 0; $i < 9; $i++) {
            $existing[] = ['type' => 'image', 'path' => "a/e{$i}.png", 'url' => "https://t/a/e{$i}.png", 'mime' => 'image/png', 'name' => "e{$i}.png"];
        }
        [$user, $task] = $this->memberTask($existing);

        $result = app(AddTaskAttachmentsService::class)->execute($user, $task->id, [
            $this->image('a/x1.png'),
            $this->image('a/x2.png'),
            $this->image('a/x3.png'),
        ]);

        // Only one of the three fits under the cap of 10.
        $this->assertSame(1, $result['attached_count']);
        $task->refresh();
        $this->assertCount(10, $task->attachments);
        $this->assertSame('a/x1.png', $task->attachments[9]['path']);
    }

    public function test_non_member_cannot_attach(): void
    {
        $task = Task::factory()->create();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(AddTaskAttachmentsService::class)->execute($stranger, $task->id, [$this->image('a/x.png')]);
    }

    public function test_rejects_malformed_attachment_missing_url(): void
    {
        [$user, $task] = $this->memberTask();

        $this->expectException(ValidationException::class);

        app(AddTaskAttachmentsService::class)->execute($user, $task->id, [
            ['path' => 'a/no-url.png'],
        ]);
    }
}
