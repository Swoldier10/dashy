<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
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

class ChatToolCallTest extends TestCase
{
    use RefreshDatabase;

    private function setupUserWithProject(): array
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

        return [$user, $project, $status];
    }

    public function test_tool_call_persisted_as_pending_then_confirm_creates_task(): void
    {
        [$user, $project, $status] = $this->setupUserWithProject();
        $this->actingAs($user);

        $args = json_encode([
            'project_id' => $project->id,
            'name' => 'Fix login bug',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_1', 'create_task');
            yield ChatStreamEvent::toolCallCompleted('fc_1', 'create_task', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'create a task to fix login bug')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertNotNull($assistant->tool_call);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame('create_task', $assistant->tool_call['name']);
        $this->assertSame($project->id, $assistant->tool_call['arguments']['project_id']);
        $this->assertSame($status->id, $assistant->tool_call['arguments']['status_id']);
        $this->assertSame(0, Task::count(), 'Tool call must NOT auto-execute.');

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Task::count());
        $task = Task::first();
        $this->assertSame('Fix login bug', $task->name);
        $this->assertSame([$user->id], $task->assignees()->pluck('users.id')->all());

        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
        $this->assertSame($task->id, $assistant->tool_call['result']['task_id']);
    }

    public function test_invalid_tool_arguments_persist_as_failed_no_task_created(): void
    {
        [$user] = $this->setupUserWithProject();
        $this->actingAs($user);

        $args = json_encode([
            'project_id' => 99999, // does not exist
            'name' => 'Phantom',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallCompleted('fc_x', 'create_task', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'create a task in nowhere')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('failed', $assistant->tool_call['status']);
        $this->assertNotEmpty($assistant->tool_call['validation_errors']);
        $this->assertSame(0, Task::count());
    }

    public function test_follow_up_message_after_pending_tool_call_sees_prior_call_in_history(): void
    {
        [$user, $project, $status] = $this->setupUserWithProject();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'create task A: fix mobile sync',
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_prev',
                'name' => 'create_task',
                'arguments' => [
                    'project_id' => $project->id,
                    'name' => 'Fix mobile project sync',
                    'status_id' => $status->id,
                    'priority' => 'high',
                    'start_date' => now()->toDateString(),
                    'end_date' => null,
                    'assignee_user_ids' => [$user->id],
                ],
                'status' => 'pending',
            ],
        ]);

        $captured = null;
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')
            ->once()
            ->andReturnUsing(function ($connection, $inputItems) use (&$captured) {
                $captured = $inputItems;
                yield from [];
            });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('message', 'now create task B: talk with mobile developer')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $this->assertIsArray($captured);
        $this->assertCount(4, $captured, 'history should be: user-msg-1, function_call, function_call_output, user-msg-2');

        $this->assertSame('message', $captured[0]['type']);
        $this->assertSame('user', $captured[0]['role']);
        $this->assertSame('create task A: fix mobile sync', $captured[0]['content'][0]['text']);

        $this->assertSame('function_call', $captured[1]['type']);
        $this->assertSame('fc_prev', $captured[1]['call_id']);
        $this->assertSame('create_task', $captured[1]['name']);
        $this->assertStringContainsString('Fix mobile project sync', $captured[1]['arguments']);

        $this->assertSame('function_call_output', $captured[2]['type']);
        $this->assertSame('fc_prev', $captured[2]['call_id']);
        $this->assertStringContainsString('Awaiting user confirmation', $captured[2]['output']);

        $this->assertSame('message', $captured[3]['type']);
        $this->assertSame('user', $captured[3]['role']);
        $this->assertSame('now create task B: talk with mobile developer', $captured[3]['content'][0]['text']);
    }

    public function test_discard_does_not_create_task(): void
    {
        [$user, $project] = $this->setupUserWithProject();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_1',
                'name' => 'create_task',
                'arguments' => [
                    'project_id' => $project->id,
                    'name' => 'Will discard',
                    'status_id' => $project->statuses()->first()->id,
                    'priority' => 'normal',
                    'start_date' => now()->toDateString(),
                    'end_date' => null,
                    'assignee_user_ids' => [$user->id],
                ],
                'status' => 'pending',
            ],
        ]);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('discardToolCall', $message->id);

        $message->refresh();
        $this->assertSame('discarded', $message->tool_call['status']);
        $this->assertSame(0, Task::count());
    }
}
