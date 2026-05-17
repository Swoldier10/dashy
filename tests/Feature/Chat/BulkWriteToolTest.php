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
 * Phase 1F round-trip: a bulk-write tool the assistant proposes lands as a
 * single pending card listing every affected row; Apply mutates all rows
 * atomically.
 */
class BulkWriteToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_move_consolidates_into_one_card_and_applies_atomically(): void
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
        $tasks = Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'project_status_id' => $todo->id,
            'created_by_user_id' => $user->id,
        ]);
        $this->actingAs($user);

        $args = json_encode([
            'task_ids' => $tasks->pluck('id')->all(),
            'target_status_id' => $done->id,
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_bulk', 'bulk_move_tasks_to_status');
            yield ChatStreamEvent::toolCallCompleted('fc_bulk', 'bulk_move_tasks_to_status', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'move all to done')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('bulk_move_tasks_to_status', $assistant->tool_call['name']);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertCount(3, $assistant->tool_call['arguments']['task_ids']);

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        // All three tasks moved atomically.
        foreach ($tasks as $task) {
            $this->assertSame($done->id, $task->fresh()->project_status_id);
        }

        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
        $this->assertSame(3, $assistant->tool_call['result']['count']);
    }
}
