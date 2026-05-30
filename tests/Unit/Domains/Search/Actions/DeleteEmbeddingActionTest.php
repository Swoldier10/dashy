<?php

namespace Tests\Unit\Domains\Search\Actions;

use App\Domains\Search\Actions\DeleteEmbeddingAction;
use App\Domains\Search\Models\Embedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEmbeddingActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_matching_source_embedding_only(): void
    {
        Embedding::create(['source_type' => 'task', 'source_id' => 1, 'team_id' => 1, 'text' => 'a', 'vector' => [0.1]]);
        Embedding::create(['source_type' => 'task', 'source_id' => 2, 'team_id' => 1, 'text' => 'b', 'vector' => [0.2]]);

        (new DeleteEmbeddingAction)->execute('task', 1);

        $this->assertDatabaseMissing('chat_embeddings', ['source_type' => 'task', 'source_id' => 1]);
        $this->assertDatabaseHas('chat_embeddings', ['source_type' => 'task', 'source_id' => 2]);
    }

    public function test_is_a_no_op_when_nothing_matches(): void
    {
        Embedding::create(['source_type' => 'task', 'source_id' => 1, 'team_id' => 1, 'text' => 'a', 'vector' => [0.1]]);

        (new DeleteEmbeddingAction)->execute('project', 999);

        $this->assertSame(1, Embedding::count());
    }
}
