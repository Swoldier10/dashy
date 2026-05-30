<?php

namespace Tests\Unit\Domains\Search\Actions;

use App\Domains\Search\Actions\FindEmbeddingsByScopeAction;
use App\Domains\Search\Models\Embedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindEmbeddingsByScopeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_scopes_to_the_given_team_ids(): void
    {
        Embedding::create(['source_type' => 'task', 'source_id' => 1, 'team_id' => 10, 'text' => 'in', 'vector' => [0.1]]);
        Embedding::create(['source_type' => 'task', 'source_id' => 2, 'team_id' => 20, 'text' => 'out', 'vector' => [0.2]]);

        $result = (new FindEmbeddingsByScopeAction)->execute(['team_ids' => [10]]);

        $this->assertSame([1], $result->pluck('source_id')->all());
    }

    public function test_includes_the_actors_own_messages_alongside_team_content(): void
    {
        Embedding::create(['source_type' => 'task', 'source_id' => 1, 'team_id' => 10, 'text' => 'team', 'vector' => [0.1]]);
        Embedding::create(['source_type' => 'message', 'source_id' => 2, 'team_id' => null, 'text' => 'mine', 'vector' => [0.2], 'metadata' => ['user_id' => 99]]);
        Embedding::create(['source_type' => 'message', 'source_id' => 3, 'team_id' => null, 'text' => 'theirs', 'vector' => [0.3], 'metadata' => ['user_id' => 1]]);

        $result = (new FindEmbeddingsByScopeAction)->execute(['team_ids' => [10], 'message_user_id' => 99]);

        $this->assertEqualsCanonicalizing([1, 2], $result->pluck('source_id')->all());
    }

    public function test_filters_by_source_type_and_respects_the_limit(): void
    {
        Embedding::create(['source_type' => 'task', 'source_id' => 1, 'team_id' => 10, 'text' => 't', 'vector' => [0.1]]);
        Embedding::create(['source_type' => 'project', 'source_id' => 2, 'team_id' => 10, 'text' => 'p', 'vector' => [0.2]]);

        $tasksOnly = (new FindEmbeddingsByScopeAction)->execute(['team_ids' => [10], 'source_types' => ['task']]);
        $this->assertSame(['task'], $tasksOnly->pluck('source_type')->unique()->all());

        $limited = (new FindEmbeddingsByScopeAction)->execute(['team_ids' => [10]], limit: 1);
        $this->assertCount(1, $limited);
    }
}
