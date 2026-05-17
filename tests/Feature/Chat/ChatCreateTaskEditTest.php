<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatCreateTaskEditTest extends TestCase
{
    use RefreshDatabase;

    private function seedScenario(): array
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        $project = Project::factory()->create(['team_id' => $team->id]);
        $notStarted = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'Backlog',
            'position' => 0,
        ]);
        $active = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'Doing',
            'position' => 1,
        ]);

        $chat = Chat::create(['user_id' => $user->id]);
        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_task',
                'name' => 'create_task',
                'arguments' => [
                    'project_id' => $project->id,
                    'name' => 'LLM Suggested Task',
                    'description' => null,
                    'status_id' => $notStarted->id,
                    'priority' => 'normal',
                    'start_date' => '2026-05-10',
                    'end_date' => null,
                    'assignee_user_ids' => [$user->id],
                    'image_attachments' => [],
                ],
                'status' => 'pending',
            ],
        ]);

        return compact('user', 'other', 'team', 'project', 'notStarted', 'active', 'chat', 'assistant');
    }

    public function test_edits_to_name_priority_and_dates_persist_when_confirmed(): void
    {
        ['user' => $user, 'chat' => $chat, 'assistant' => $assistant] = $this->seedScenario();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.name', 'Edited Task')
            ->set('toolCallEdits.'.$assistant->id.'.priority', 'high')
            ->set('toolCallEdits.'.$assistant->id.'.start_date', '2026-06-01')
            ->set('toolCallEdits.'.$assistant->id.'.end_date', '2026-06-15')
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Task::count());
        $task = Task::first();
        $this->assertSame('Edited Task', $task->name);
        $this->assertSame('high', $task->priority->value);
        $this->assertSame('2026-06-01', $task->start_date->toDateString());
        $this->assertSame('2026-06-15', $task->end_date->toDateString());
    }

    public function test_edited_assignees_persist_when_confirmed(): void
    {
        ['user' => $user, 'other' => $other, 'chat' => $chat, 'assistant' => $assistant] = $this->seedScenario();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.assignee_user_ids', [$other->id])
            ->call('confirmToolCall', $assistant->id);

        $task = Task::first();
        $this->assertNotNull($task);
        $this->assertSame([$other->id], $task->assignees()->pluck('users.id')->all());
    }

    public function test_edited_status_persists_when_confirmed(): void
    {
        ['user' => $user, 'active' => $active, 'chat' => $chat, 'assistant' => $assistant] = $this->seedScenario();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.status_id', $active->id)
            ->call('confirmToolCall', $assistant->id);

        $task = Task::first();
        $this->assertNotNull($task);
        $this->assertSame($active->id, $task->project_status_id);
    }
}
