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
 * Phase 1B + 1D integration: validates the new multi-tool-per-turn loop.
 *  - The assistant can emit several tool calls in one stream.
 *  - Each is persisted as its own assistant message with parent_user_message_id.
 *  - auto_read tools execute immediately (status=executed, result populated).
 *  - confirm_write tools stay pending (no DB write until confirmation).
 */
class MultiToolTurnTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0:User,1:Project,2:ProjectStatus} */
    private function scenario(): array
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
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
        ]);
        Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'created_by_user_id' => $user->id,
        ]);

        return [$user, $project, $status];
    }

    public function test_auto_read_and_confirm_write_tools_fan_out_in_one_turn(): void
    {
        [$user, $project, $status] = $this->scenario();
        $this->actingAs($user);

        $listArgs = json_encode(['project_id' => $project->id]);
        $createArgs = json_encode([
            'project_id' => $project->id,
            'name' => 'Follow-up after list',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($listArgs, $createArgs) {
            // First (and final) LLM turn: emit one read + one write side-by-side.
            yield ChatStreamEvent::textDelta('Checking…');
            yield ChatStreamEvent::toolCallStarted('fc_list', 'list_tasks');
            yield ChatStreamEvent::toolCallCompleted('fc_list', 'list_tasks', $listArgs);
            yield ChatStreamEvent::toolCallStarted('fc_create', 'create_task');
            yield ChatStreamEvent::toolCallCompleted('fc_create', 'create_task', $createArgs);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'show me my tasks then create a follow-up')
            ->call('sendMessage')
            ->call('processAssistantReply');

        // One text row + two tool-call rows, all parented to the user's message.
        $assistantRows = Message::where('role', 'assistant')->orderBy('id')->get();
        $this->assertCount(3, $assistantRows);

        $textRow = $assistantRows[0];
        $this->assertSame('Checking…', $textRow->content);
        $this->assertNull($textRow->tool_call);

        $readRow = $assistantRows[1];
        $this->assertSame('list_tasks', $readRow->tool_call['name']);
        $this->assertSame('executed', $readRow->tool_call['status'], 'auto_read should execute inline.');
        $this->assertArrayHasKey('result', $readRow->tool_call);
        $this->assertSame($project->id, $readRow->tool_call['result']['project_id']);
        $this->assertSame(1, $readRow->tool_call['result']['count']);

        $writeRow = $assistantRows[2];
        $this->assertSame('create_task', $writeRow->tool_call['name']);
        $this->assertSame('pending', $writeRow->tool_call['status'], 'confirm_write should stay pending.');

        // All three share the same parent user message id.
        $userMessage = Message::where('role', 'user')->firstOrFail();
        foreach ($assistantRows as $row) {
            $this->assertSame(
                $userMessage->id,
                $row->parent_user_message_id,
                'every row in this turn should point at the user message.',
            );
        }

        // The write has NOT created a task — only the user can confirm that.
        $this->assertSame(1, Task::count(), 'create_task must stay pending until confirmation.');
    }

    public function test_turn_iteration_cap_stops_runaway_loops(): void
    {
        [$user, $project, $status] = $this->scenario();
        $this->actingAs($user);

        $listArgs = json_encode(['project_id' => $project->id]);

        // Every iteration of the stream emits one auto_read call so the
        // loop would otherwise spin forever. The cap should halt it.
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($listArgs) {
            yield ChatStreamEvent::toolCallStarted('fc_loop', 'list_tasks');
            yield ChatStreamEvent::toolCallCompleted('fc_loop', 'list_tasks', $listArgs);
        });
        $this->app->instance(CodexClient::class, $mock);

        $component = Livewire::test('chat.chat-panel')
            ->set('message', 'forever loop')
            ->call('sendMessage');

        // Manually run processAssistantReply until the cap. The dispatch-back
        // pattern means each call simulates one LLM round.
        for ($i = 0; $i < 7; $i++) {
            $component->call('processAssistantReply');
        }

        // 6 successful iterations max. The 7th call hits the cap and stops
        // without persisting another tool-call row.
        $toolRows = Message::whereNotNull('tool_call')->count();
        $this->assertLessThanOrEqual(6, $toolRows);
    }
}
