<?php

namespace Tests\Feature\Console;

use App\Domains\Projects\Models\Project;
use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmbedBackfillCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_queues_an_embed_job_per_source_and_reports_totals(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(2)->create(['project_id' => $project->id]);

        // Fake AFTER seeding so the EmbeddingObserver's save-time dispatches
        // don't pollute the count — we assert only what the command queues.
        Queue::fake();

        $this->artisan('dashy:embed-backfill', ['--scope' => 'tasks'])
            ->expectsOutputToContain('Queued 2 task embed jobs')
            ->assertExitCode(0);

        Queue::assertPushed(EmbedSourceJob::class, 2);
    }

    public function test_rejects_an_invalid_scope(): void
    {
        Queue::fake();

        $this->artisan('dashy:embed-backfill', ['--scope' => 'bogus'])
            ->assertExitCode(2); // Command::INVALID

        Queue::assertNothingPushed();
    }
}
