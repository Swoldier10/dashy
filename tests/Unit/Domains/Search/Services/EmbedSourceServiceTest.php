<?php

namespace Tests\Unit\Domains\Search\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Search\Actions\DeleteEmbeddingAction;
use App\Domains\Search\Actions\UpsertEmbeddingAction;
use App\Domains\Search\Services\EmbedSourceService;
use App\Domains\Search\Services\EmbedTextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EmbedSourceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_upserts_when_source_loads(): void
    {
        $project = Project::factory()->create(['name' => 'Foo', 'description' => 'bar']);

        $embed = Mockery::mock(EmbedTextService::class);
        $embed->shouldReceive('isConfigured')->andReturn(true);
        $embed->shouldReceive('embed')->andReturn([0.1, 0.2, 0.3]);
        $this->app->instance(EmbedTextService::class, $embed);

        $upsert = Mockery::mock(UpsertEmbeddingAction::class);
        $upsert->shouldReceive('execute')
            ->once()
            ->with('project', $project->id, $project->team_id, Mockery::type('string'), [0.1, 0.2, 0.3], Mockery::type('array'));
        $this->app->instance(UpsertEmbeddingAction::class, $upsert);

        app(EmbedSourceService::class)->execute('project', $project->id);
    }

    public function test_deletes_when_source_gone(): void
    {
        $embed = Mockery::mock(EmbedTextService::class);
        $embed->shouldReceive('isConfigured')->andReturn(true);
        $embed->shouldNotReceive('embed');
        $this->app->instance(EmbedTextService::class, $embed);

        $delete = Mockery::mock(DeleteEmbeddingAction::class);
        $delete->shouldReceive('execute')->once()->with('project', 999_999);
        $this->app->instance(DeleteEmbeddingAction::class, $delete);

        app(EmbedSourceService::class)->execute('project', 999_999);
    }

    public function test_short_circuits_when_unconfigured(): void
    {
        $embed = Mockery::mock(EmbedTextService::class);
        $embed->shouldReceive('isConfigured')->andReturn(false);
        $embed->shouldNotReceive('embed');
        $this->app->instance(EmbedTextService::class, $embed);

        $upsert = Mockery::mock(UpsertEmbeddingAction::class);
        $upsert->shouldNotReceive('execute');
        $this->app->instance(UpsertEmbeddingAction::class, $upsert);

        app(EmbedSourceService::class)->execute('project', 1);
    }

    public function test_forget_deletes_the_embedding_through_a_transaction(): void
    {
        // forget() is the path the EmbeddingObserver uses on delete; it must
        // route through the Action and wrap the write in a transaction.
        $delete = Mockery::mock(DeleteEmbeddingAction::class);
        $delete->shouldReceive('execute')->once()->with('task', 42);
        $this->app->instance(DeleteEmbeddingAction::class, $delete);

        app(EmbedSourceService::class)->forget('task', 42);
    }
}
