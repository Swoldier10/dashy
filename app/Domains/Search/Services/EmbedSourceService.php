<?php

namespace App\Domains\Search\Services;

use App\Domains\Search\Actions\DeleteEmbeddingAction;
use App\Domains\Search\Actions\FindEmbeddableSourceAction;
use App\Domains\Search\Actions\UpsertEmbeddingAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Embed (or re-embed) a single source row. Idempotent on
 * (source_type, source_id) — UpsertEmbeddingAction replaces in place.
 * When the source row no longer exists (deleted between dispatch and run),
 * the existing embedding is removed.
 */
final class EmbedSourceService
{
    public function __construct(
        private FindEmbeddableSourceAction $findSource,
        private EmbedTextService $embed,
        private UpsertEmbeddingAction $upsert,
        private DeleteEmbeddingAction $delete,
    ) {}

    public function execute(string $sourceType, int $sourceId): void
    {
        if (! $this->embed->isConfigured()) {
            return;
        }

        $payload = $this->findSource->execute($sourceType, $sourceId);

        if ($payload === null) {
            DB::transaction(fn () => $this->delete->execute($sourceType, $sourceId));

            return;
        }

        try {
            $vector = $this->embed->embed($payload['text']);
        } catch (Throwable $e) {
            Log::warning('EmbedSourceService failed to embed', [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        DB::transaction(fn () => $this->upsert->execute(
            $sourceType,
            $sourceId,
            $payload['team_id'],
            $payload['text'],
            $vector,
            $payload['metadata'],
        ));
    }

    /**
     * Remove the embedding for a source row that has been deleted. Routed
     * through the service (rather than calling the Action from the observer)
     * so the delete write owns a transaction boundary.
     */
    public function forget(string $sourceType, int $sourceId): void
    {
        DB::transaction(fn () => $this->delete->execute($sourceType, $sourceId));
    }
}
