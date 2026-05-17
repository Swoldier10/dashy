<?php

namespace Tests\Feature\Chat\Eval;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Eval: assistant chains a read + a write in the same turn. The read
 * (list_tasks) executes inline; the write (move_task_to_status) lands as
 * pending. The runtime must persist both, and parent both to the same
 * user message.
 */
class ReadThenWriteChainEvalTest extends ChatEvalTestCase
{
    use RefreshDatabase;

    public function test_eval(): void
    {
        $user = User::factory()->create();
        CodexConnection::create(['user_id' => $user->id, 'access_token' => 'a', 'expires_at' => now()->addHour()]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $todo = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => 'not_started']);
        $done = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => 'done']);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $todo->id,
            'created_by_user_id' => $user->id,
        ]);
        $this->actingAs($user);

        $this->fakeCodexStream([
            ChatStreamEvent::toolCallStarted('fc_list', 'list_tasks'),
            ChatStreamEvent::toolCallCompleted('fc_list', 'list_tasks', json_encode(['project_id' => $project->id])),
            ChatStreamEvent::toolCallStarted('fc_move', 'move_task_to_status'),
            ChatStreamEvent::toolCallCompleted('fc_move', 'move_task_to_status', json_encode([
                'task_id' => $task->id,
                'target_status_id' => $done->id,
            ])),
        ]);

        $this->runOneTurn('Show me my tasks then move that one to Done.');

        $assistant = Message::where('role', 'assistant')->whereNotNull('tool_call')->get();
        $this->assertCount(2, $assistant, 'one row per tool call.');

        $list = $assistant->firstWhere(fn ($m) => $m->tool_call['name'] === 'list_tasks');
        $this->assertSame('executed', $list->tool_call['status']);

        $move = $assistant->firstWhere(fn ($m) => $m->tool_call['name'] === 'move_task_to_status');
        $this->assertSame('pending', $move->tool_call['status']);

        // Same parent user message — the whole turn is grouped.
        $this->assertSame($list->parent_user_message_id, $move->parent_user_message_id);
    }
}
