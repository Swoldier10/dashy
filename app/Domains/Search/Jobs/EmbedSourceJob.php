<?php

namespace App\Domains\Search\Jobs;

use App\Domains\Search\Services\EmbedSourceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Embed (or re-embed) a single source row. Idempotent on (source_type,
 * source_id) — UpsertEmbeddingAction replaces in place. Dispatched from the
 * Task/Project/Message save listeners and from the dashy:embed-backfill
 * command. The service is responsible for loading + embedding + upserting.
 */
class EmbedSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $sourceType,
        public int $sourceId,
    ) {}

    public function handle(EmbedSourceService $embed): void
    {
        $embed->execute($this->sourceType, $this->sourceId);
    }
}
