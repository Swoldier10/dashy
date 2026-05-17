<?php

namespace Tests\Unit\Domains\Search\Services;

use App\Domains\Search\Models\Embedding;
use App\Domains\Search\Services\EmbedTextService;
use App\Domains\Search\Services\SemanticSearchService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for the cosine-ranking + team-scoping logic. The OpenAI HTTP
 * call is mocked through a faked EmbedTextService that returns a
 * predetermined query vector — we measure ranking only, not the actual API.
 */
class SemanticSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cosine_similarity_orders_results_by_proximity(): void
    {
        $a = [1, 0, 0];
        $b = [1, 0, 0];   // identical → 1.0
        $c = [0, 1, 0];   // orthogonal → 0.0
        $d = [-1, 0, 0];  // opposite → -1.0

        $this->assertEqualsWithDelta(1.0, SemanticSearchService::cosineSimilarity($a, $b), 1e-9);
        $this->assertEqualsWithDelta(0.0, SemanticSearchService::cosineSimilarity($a, $c), 1e-9);
        $this->assertEqualsWithDelta(-1.0, SemanticSearchService::cosineSimilarity($a, $d), 1e-9);
    }

    public function test_search_returns_team_scoped_results_ranked_by_similarity(): void
    {
        $user = User::factory()->create();
        $myTeam = Team::factory()->create();
        $myTeam->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $otherTeam = Team::factory()->create();
        $otherUser = User::factory()->create();
        $otherTeam->members()->attach($otherUser->id, ['role' => TeamRole::Owner->value]);

        // 3 embeddings: two in my team, one in the other team.
        Embedding::create([
            'source_type' => 'task',
            'source_id' => 1,
            'team_id' => $myTeam->id,
            'text' => 'near-match',
            'vector' => [0.99, 0.01, 0.0],
        ]);
        Embedding::create([
            'source_type' => 'task',
            'source_id' => 2,
            'team_id' => $myTeam->id,
            'text' => 'far-match',
            'vector' => [0.1, 0.9, 0.0],
        ]);
        Embedding::create([
            'source_type' => 'task',
            'source_id' => 3,
            'team_id' => $otherTeam->id,
            'text' => 'other team — should be hidden',
            'vector' => [1.0, 0.0, 0.0],
        ]);

        // Mock the query embedding service — the OpenAI API call is irrelevant
        // here; we measure ranking only.
        $mock = Mockery::mock(EmbedTextService::class);
        $mock->shouldReceive('isConfigured')->andReturnTrue();
        $mock->shouldReceive('embed')->andReturn([1.0, 0.0, 0.0]);
        $this->app->instance(EmbedTextService::class, $mock);

        $results = app(SemanticSearchService::class)->execute($user, 'anything');

        $this->assertCount(2, $results, 'leak check: other team embeddings excluded.');
        $this->assertSame(1, $results[0]['source_id'], 'near-match comes first.');
        $this->assertSame(2, $results[1]['source_id']);
        $this->assertGreaterThan($results[1]['score'], $results[0]['score']);
    }

    public function test_search_returns_empty_when_api_key_missing(): void
    {
        config(['services.openai.api_key' => '']);
        $user = User::factory()->create();

        $results = app(SemanticSearchService::class)->execute($user, 'anything');
        $this->assertSame([], $results);
    }
}
