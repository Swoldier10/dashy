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
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ChatCreateProjectTest extends TestCase
{
    use RefreshDatabase;

    private function memberOfOneTeam(): array
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        return [$user, $team];
    }

    public function test_tool_call_persisted_as_pending_then_confirm_creates_project_with_default_statuses(): void
    {
        [$user, $team] = $this->memberOfOneTeam();
        $this->actingAs($user);

        $args = json_encode([
            'team_id' => $team->id,
            'name' => 'Marketing-Website Relaunch',
            'description' => 'Neue Seite mit Blog.',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_p1', 'create_project');
            yield ChatStreamEvent::toolCallCompleted('fc_p1', 'create_project', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'erstelle ein projekt namens marketing website relaunch')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertNotNull($assistant->tool_call);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame('create_project', $assistant->tool_call['name']);
        $this->assertSame($team->id, $assistant->tool_call['arguments']['team_id']);
        $this->assertSame('Marketing-Website Relaunch', $assistant->tool_call['arguments']['name']);
        $this->assertSame(0, Project::count(), 'Tool call must NOT auto-execute.');

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Project::count());
        $project = Project::firstOrFail();
        $this->assertSame('Marketing-Website Relaunch', $project->name);
        $this->assertSame('Neue Seite mit Blog.', $project->description);
        $this->assertSame($team->id, $project->team_id);

        $statuses = ProjectStatus::query()
            ->where('project_id', $project->id)
            ->get()
            ->mapWithKeys(fn (ProjectStatus $s) => [$s->category->value => $s->name])
            ->all();
        $this->assertSame('Zu erledigen', $statuses[ProjectStatusCategory::NotStarted->value]);
        $this->assertSame('In Bearbeitung', $statuses[ProjectStatusCategory::Active->value]);
        $this->assertSame('Erledigt', $statuses[ProjectStatusCategory::Done->value]);

        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
        $this->assertSame($project->id, $assistant->tool_call['result']['project_id']);
    }

    public function test_invalid_team_id_persists_as_failed_no_project_created(): void
    {
        [$user] = $this->memberOfOneTeam();
        $this->actingAs($user);

        $args = json_encode([
            'team_id' => 99999, // does not exist
            'name' => 'Phantom',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallCompleted('fc_px', 'create_project', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'erstelle projekt im falschen team')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('failed', $assistant->tool_call['status']);
        $this->assertNotEmpty($assistant->tool_call['validation_errors']);
        $this->assertSame(0, Project::count());
    }

    public function test_discard_does_not_create_project(): void
    {
        [$user, $team] = $this->memberOfOneTeam();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_p2',
                'name' => 'create_project',
                'arguments' => [
                    'team_id' => $team->id,
                    'name' => 'Will discard',
                    'description' => null,
                    'logo_attachment' => null,
                ],
                'status' => 'pending',
            ],
        ]);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('discardToolCall', $message->id);

        $message->refresh();
        $this->assertSame('discarded', $message->tool_call['status']);
        $this->assertSame(0, Project::count());
    }
}
