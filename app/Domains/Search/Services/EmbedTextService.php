<?php

namespace App\Domains\Search\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin wrapper around OpenAI's /v1/embeddings endpoint. Returns the vector
 * for one string at a time — callers chunk longer documents themselves. The
 * server-side OPENAI_API_KEY is reused (already configured for Whisper); no
 * per-user OAuth is involved because embeddings run from queues + backfill
 * commands where no user is online.
 */
class EmbedTextService
{
    public function isConfigured(): bool
    {
        return (string) config('services.openai.api_key') !== '';
    }

    /**
     * Embed a single text string. Returns the float vector (1536 dimensions
     * for text-embedding-3-small by default). Throws when the API key is
     * missing or the request fails — callers should catch and decide whether
     * to retry or skip.
     *
     * @return list<float>
     */
    public function embed(string $text): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OPENAI_API_KEY is not configured; cannot embed.');
        }

        $text = trim($text);
        if ($text === '') {
            throw new RuntimeException('Cannot embed an empty string.');
        }

        $response = Http::withToken((string) config('services.openai.api_key'))
            ->timeout((int) config('services.openai.embedding_timeout', 30))
            ->acceptJson()
            ->asJson()
            ->post((string) config('services.openai.embedding_url'), [
                'model' => (string) config('services.openai.embedding_model'),
                'input' => $text,
            ]);

        if ($response->failed()) {
            Log::warning('OpenAI embedding request failed', [
                'status' => $response->status(),
                'body' => substr((string) $response->body(), 0, 500),
            ]);
            throw new RuntimeException(sprintf(
                'Embedding API error %d: %s',
                $response->status(),
                substr((string) $response->body(), 0, 200) ?: '(empty body)',
            ));
        }

        $vector = $response->json('data.0.embedding');
        if (! is_array($vector) || $vector === []) {
            throw new RuntimeException('Embedding API returned an empty vector.');
        }

        return array_map(static fn ($v) => (float) $v, $vector);
    }
}
