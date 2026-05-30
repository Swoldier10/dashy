<?php

namespace Tests\Unit\Domains\Search\Actions;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Actions\FindEmbeddableSourceAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindEmbeddableSourceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_loads_task_with_project_team_id(): void
    {
        $project = Project::factory()->create(['name' => 'Acme']);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Do the thing',
            'description' => 'extra details',
        ]);

        $result = (new FindEmbeddableSourceAction)->execute('task', $task->id);

        $this->assertNotNull($result);
        $this->assertSame($project->team_id, $result['team_id']);
        $this->assertStringContainsString('Do the thing', $result['text']);
        $this->assertStringContainsString('extra details', $result['text']);
        $this->assertSame('Acme', $result['metadata']['project_name']);
    }

    public function test_returns_null_for_missing_task(): void
    {
        $this->assertNull((new FindEmbeddableSourceAction)->execute('task', 999_999));
    }

    public function test_loads_project(): void
    {
        $project = Project::factory()->create(['name' => 'Mars', 'description' => 'red one']);

        $result = (new FindEmbeddableSourceAction)->execute('project', $project->id);

        $this->assertNotNull($result);
        $this->assertSame($project->team_id, $result['team_id']);
        $this->assertSame('Mars', $result['metadata']['name']);
    }

    public function test_loads_message_skips_summary(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 't']);
        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'hello world',
            'is_summary' => false,
        ]);

        $result = (new FindEmbeddableSourceAction)->execute('message', $message->id);

        $this->assertNotNull($result);
        $this->assertSame('hello world', $result['text']);
        $this->assertSame($user->id, $result['metadata']['user_id']);
        $this->assertNull($result['team_id']);
    }

    public function test_throws_for_unknown_type(): void
    {
        $this->expectException(\RuntimeException::class);

        (new FindEmbeddableSourceAction)->execute('widget', 1);
    }
}
