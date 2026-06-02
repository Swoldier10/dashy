<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\AttachImageToTaskTool;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachImageToTaskToolTest extends TestCase
{
    use RefreshDatabase;

    private function memberTask(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        return [$user, $task];
    }

    private function chatWithImage(User $user): Chat
    {
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'attach this',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/shot.png',
                'url' => 'https://test/shot.png',
                'mime' => 'image/png',
                'name' => 'shot.png',
            ]],
        ]);

        return $chat;
    }

    public function test_validate_rejects_non_integer_task_id(): void
    {
        [$user] = $this->memberTask();
        $chat = $this->chatWithImage($user);

        $result = app(AttachImageToTaskTool::class)->validate($user, ['task_id' => 'abc'], $chat);

        $this->assertFalse($result->valid);
        $this->assertSame('task_id is required and must be an integer.', $result->errors[0]);
    }

    public function test_validate_fails_when_no_image_in_conversation(): void
    {
        [$user, $task] = $this->memberTask();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'no image here']);

        $result = app(AttachImageToTaskTool::class)->validate($user, ['task_id' => $task->id], $chat);

        $this->assertFalse($result->valid);
        $this->assertSame('No image found in this conversation to attach.', $result->errors[0]);
    }

    public function test_validate_fails_for_inaccessible_task(): void
    {
        [$user] = $this->memberTask();
        $chat = $this->chatWithImage($user);
        $foreignTask = Task::factory()->create(); // different team, no access

        $result = app(AttachImageToTaskTool::class)->validate($user, ['task_id' => $foreignTask->id], $chat);

        $this->assertFalse($result->valid);
        $this->assertSame('You do not have access to that task.', $result->errors[0]);
    }

    public function test_validate_snapshots_image_from_chat(): void
    {
        [$user, $task] = $this->memberTask();
        $chat = $this->chatWithImage($user);

        $result = app(AttachImageToTaskTool::class)->validate($user, ['task_id' => $task->id], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame($task->id, $result->normalized['task_id']);
        $this->assertCount(1, $result->normalized['image_attachments']);
        $this->assertSame('chat-attachments/x/shot.png', $result->normalized['image_attachments'][0]['path']);
    }

    public function test_validate_preserves_image_attachments_during_revalidation(): void
    {
        [$user, $task] = $this->memberTask();
        // The chat now holds a DIFFERENT image than the snapshot in args.
        $chat = $this->chatWithImage($user);

        $result = app(AttachImageToTaskTool::class)->validate($user, [
            'task_id' => $task->id,
            'image_attachments' => [[
                'path' => 'chat-attachments/x/original.png',
                'url' => 'https://test/original.png',
                'mime' => 'image/png',
                'name' => 'original.png',
            ]],
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('chat-attachments/x/original.png', $result->normalized['image_attachments'][0]['path']);
    }

    public function test_execute_attaches_image_and_returns_count(): void
    {
        [$user, $task] = $this->memberTask();

        $result = app(AttachImageToTaskTool::class)->execute($user, [
            'task_id' => $task->id,
            'image_attachments' => [[
                'path' => 'chat-attachments/x/shot.png',
                'url' => 'https://test/shot.png',
                'mime' => 'image/png',
                'name' => 'shot.png',
            ]],
        ]);

        $this->assertSame($task->id, $result['task_id']);
        $this->assertSame(1, $result['attached_count']);
        $task->refresh();
        $this->assertSame('chat-attachments/x/shot.png', $task->attachments[0]['path']);
    }
}
