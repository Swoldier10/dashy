<?php

namespace Tests\Feature\Chat\Eval;

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

/**
 * Eval: user clearly asks for a new task in their only project.
 * Expected: assistant proposes a `create_task` card; user confirms; task lands.
 */
class CreateTaskFromExplicitRequestEvalTest extends ChatEvalTestCase
{
    use RefreshDatabase;

    public function test_eval(): void
    {
        $user = User::factory()->create();
        CodexConnection::create(['user_id' => $user->id, 'access_token' => 'a', 'expires_at' => now()->addHour()]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
        ]);
        $this->actingAs($user);

        $this->fakeCodexStream($this->toolCallStream('fc_1', 'create_task', [
            'project_id' => $project->id,
            'name' => 'Login-Bug beheben',
            'description' => "## Beschreibung\nLogin schlägt fehl.\n\n## Akzeptanzkriterien\n- Login funktioniert.",
        ]));

        $this->runOneTurn('Erstelle eine Aufgabe: Login-Bug beheben.');

        $msg = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('create_task', $msg->tool_call['name']);
        $this->assertSame('pending', $msg->tool_call['status']);
        $this->assertSame(0, Task::count());

        // Confirm — task lands.
        \Livewire\Livewire::test('chat.chat-panel', ['chat' => $msg->chat_id])
            ->call('confirmToolCall', $msg->id);

        $this->assertSame(1, Task::count());
        $this->assertSame('Login-Bug beheben', Task::first()->name);
    }
}
