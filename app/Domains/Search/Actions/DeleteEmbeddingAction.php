<?php

namespace App\Domains\Search\Actions;

use App\Domains\Search\Models\Embedding;

class DeleteEmbeddingAction
{
    public function execute(string $sourceType, int $sourceId): void
    {
        Embedding::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }
}
