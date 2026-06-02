<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttachImageToTaskTest extends TestCase
{
    use RefreshDatabase;

    private function seedScenario(bool $withImage = true): array
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'attach the image to that task',
            'attachments' => $withImage ? [[
                'type' => 'image',
                'path' => 'chat-attachments/x/shot.png',
                'url' => 'https://test/shot.png',
                'mime' => 'image/png',
                'name' => 'shot.png',
            ]] : null,
        ]);

        // Pending attach card whose arguments OMIT image_attachments, so confirm
        // re-validates and snapshots from the conversation.
        $pending = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_attach',
                'name' => 'attach_image_to_task',
                'arguments' => ['task_id' => $task->id],
                'status' => 'pending',
            ],
        ]);

        return compact('user', 'task', 'chat', 'pending');
    }

    public function test_confirming_attaches_the_conversation_image_to_the_task(): void
    {
        ['user' => $user, 'task' => $task, 'chat' => $chat, 'pending' => $pending] = $this->seedScenario();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('confirmToolCall', $pending->id);

        $task->refresh();
        $this->assertNotNull($task->attachments);
        $this->assertSame('chat-attachments/x/shot.png', $task->attachments[0]['path']);

        $pending->refresh();
        $this->assertSame('created', $pending->tool_call['status']);
        $this->assertSame(1, $pending->tool_call['result']['attached_count']);
    }

    public function test_no_image_in_conversation_keeps_the_card_pending_with_an_error(): void
    {
        ['user' => $user, 'task' => $task, 'chat' => $chat, 'pending' => $pending] = $this->seedScenario(withImage: false);
        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('confirmToolCall', $pending->id);

        $task->refresh();
        $this->assertNull($task->attachments);

        $pending->refresh();
        $this->assertSame('pending', $pending->tool_call['status']);
        $this->assertContains('No image found in this conversation to attach.', $pending->tool_call['validation_errors']);
    }
}
