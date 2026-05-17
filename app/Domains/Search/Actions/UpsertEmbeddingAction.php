<?php

namespace App\Domains\Search\Actions;

use App\Domains\Search\Models\Embedding;

class UpsertEmbeddingAction
{
    /**
     * Insert-or-update by (source_type, source_id). Used by the embedding
     * job so re-saves of the same task/project/message replace the vector
     * in place rather than spawning duplicates.
     *
     * @param  list<float>  $vector
     * @param  array<string, mixed>|null  $metadata
     */
    public function execute(
        string $sourceType,
        int $sourceId,
        ?int $teamId,
        string $text,
        array $vector,
        ?array $metadata = null,
    ): Embedding {
        $existing = Embedding::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();

        $attributes = [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'team_id' => $teamId,
            'text' => $text,
            'vector' => $vector,
            'metadata' => $metadata,
        ];

        if ($existing !== null) {
            $existing->forceFill($attributes)->save();

            return $existing;
        }

        return Embedding::create($attributes);
    }
}
