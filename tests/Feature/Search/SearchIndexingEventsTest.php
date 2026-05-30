<?php

namespace Tests\Feature\Search;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Search\Models\Embedding;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The owning domains emit content-change events; Search's listener keeps the
 * embedding index in sync. Verifies the wiring end-to-end (model save/delete
 * → domain event → IndexChangedSource → queue/forget) without Search ever
 * observing the foreign models.
 */
class SearchIndexingEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_task_queues_an_embed_job_for_it(): void
    {
        $project = Project::factory()->create();
        Queue::fake();

        $task = Task::factory()->create(['project_id' => $project->id]);

        Queue::assertPushed(
            EmbedSourceJob::class,
            fn (EmbedSourceJob $job) => $job->sourceType === 'task' && $job->sourceId === (int) $task->id,
        );
    }

    public function test_deleting_a_task_removes_its_embedding(): void
    {
        $task = Task::factory()->create();
        Embedding::create([
            'source_type' => 'task',
            'source_id' => $task->id,
            'team_id' => 1,
            'text' => 'x',
            'vector' => [0.1],
        ]);

        $task->delete();

        $this->assertDatabaseMissing('chat_embeddings', ['source_type' => 'task', 'source_id' => $task->id]);
    }

    public function test_a_summary_message_is_not_indexed(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'c']);
        Queue::fake();

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => 'compacted history…',
            'is_summary' => true,
        ]);

        Queue::assertNotPushed(EmbedSourceJob::class);
    }

    public function test_a_content_bearing_message_is_indexed(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'c']);
        Queue::fake();

        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'Find the onboarding tasks',
        ]);

        Queue::assertPushed(
            EmbedSourceJob::class,
            fn (EmbedSourceJob $job) => $job->sourceType === 'message' && $job->sourceId === (int) $message->id,
        );
    }
}
