<?php

namespace Tests\Unit\Domains\Search\Actions;

use App\Domains\Search\Actions\UpsertEmbeddingAction;
use App\Domains\Search\Models\Embedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpsertEmbeddingActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_embedding_when_none_exists(): void
    {
        $embedding = (new UpsertEmbeddingAction)->execute('task', 1, 7, 'hello', [0.1, 0.2], ['k' => 'v']);

        $this->assertSame('task', $embedding->source_type);
        $this->assertSame([0.1, 0.2], $embedding->vector);
        $this->assertDatabaseHas('chat_embeddings', ['source_type' => 'task', 'source_id' => 1]);
    }

    public function test_replaces_the_existing_row_in_place(): void
    {
        (new UpsertEmbeddingAction)->execute('task', 1, 7, 'old', [0.1], null);

        (new UpsertEmbeddingAction)->execute('task', 1, 7, 'new', [0.9], null);

        $this->assertSame(1, Embedding::query()->where('source_type', 'task')->where('source_id', 1)->count());
        $row = Embedding::query()->where('source_type', 'task')->where('source_id', 1)->first();
        $this->assertSame('new', $row->text);
        $this->assertSame([0.9], $row->vector);
    }
}
