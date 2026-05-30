<?php

namespace Tests\Unit\Domains\Search\Actions;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Actions\ListBackfillSourceIdsAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBackfillSourceIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_streams_task_ids(): void
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();

        $ids = iterator_to_array((new ListBackfillSourceIdsAction)->execute('task'));

        sort($ids);
        $expected = [$taskA->id, $taskB->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    public function test_streams_project_ids(): void
    {
        $project = Project::factory()->create();

        $ids = iterator_to_array((new ListBackfillSourceIdsAction)->execute('project'));

        $this->assertContains($project->id, $ids);
    }

    public function test_message_stream_skips_tool_calls_and_summaries(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 't']);
        $keep = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'hi',
            'is_summary' => false,
        ]);
        $summary = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '...',
            'is_summary' => true,
        ]);
        $toolCall = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'is_summary' => false,
            'tool_call' => ['name' => 'x'],
        ]);

        $ids = iterator_to_array((new ListBackfillSourceIdsAction)->execute('message'));

        $this->assertContains($keep->id, $ids);
        $this->assertNotContains($summary->id, $ids);
        $this->assertNotContains($toolCall->id, $ids);
    }

    public function test_throws_for_unknown_type(): void
    {
        $this->expectException(\RuntimeException::class);

        iterator_to_array((new ListBackfillSourceIdsAction)->execute('widget'));
    }
}
