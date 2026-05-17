<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Phase 1E round-trip: a confirm_write tool the assistant proposes lands as
 * a pending card; the user clicks Apply; the underlying service executes.
 */
class CompactWriteToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_move_task_to_status_applies_via_card(): void
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
        $todo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
        ]);
        $done = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $todo->id,
            'created_by_user_id' => $user->id,
        ]);
        $this->actingAs($user);

        $args = json_encode(['task_id' => $task->id, 'target_status_id' => $done->id]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_mv', 'move_task_to_status');
            yield ChatStreamEvent::toolCallCompleted('fc_mv', 'move_task_to_status', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'move that task to done')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('move_task_to_status', $assistant->tool_call['name']);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame($todo->id, $task->fresh()->project_status_id, 'no DB change until confirm');

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame($done->id, $task->fresh()->project_status_id);
        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
    }
}
