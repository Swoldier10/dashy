<?php

namespace App\Domains\Search\Services;

use App\Domains\Search\Actions\FindEmbeddingsByScopeAction;
use App\Domains\Search\Models\Embedding;
use App\Domains\Teams\Services\ListTeamIdsForUserService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Embeds a query, fetches a coarse candidate set (team-scoped + user-scoped),
 * and re-ranks it by cosine similarity to the query vector. Returns the top
 * K matches with their source type/id, a short snippet, and the score so the
 * AI chat can decide what to do with them.
 */
final class SemanticSearchService
{
    public function __construct(
        private EmbedTextService $embedText,
        private FindEmbeddingsByScopeAction $findByScope,
        private ListTeamIdsForUserService $listTeamIds,
    ) {}

    /**
     * @param  list<string>|null  $scopes  one or more of "task" | "project" | "message"
     * @return list<array{source_type: string, source_id: int, snippet: string, score: float, metadata: array<string, mixed>|null}>
     */
    public function execute(User $actor, string $query, ?array $scopes = null, ?int $teamId = null, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        if (! $this->embedText->isConfigured()) {
            return [];
        }

        try {
            $queryVector = $this->embedText->embed($query);
        } catch (Throwable $e) {
            Log::warning('Semantic search query embedding failed', ['error' => $e->getMessage()]);

            return [];
        }

        $teamIds = $this->teamIdsFor($actor, $teamId);
        if ($teamIds === [] && ! $this->hasOwnMessages($actor)) {
            return [];
        }

        $filters = [
            'team_ids' => $teamIds,
            'message_user_id' => $actor->id,
        ];
        if ($scopes !== null && $scopes !== []) {
            $filters['source_types'] = $scopes;
        }

        $candidates = $this->findByScope->execute($filters, limit: 500);

        $ranked = $candidates
            ->map(fn (Embedding $e) => [
                'embedding' => $e,
                'score' => self::cosineSimilarity($queryVector, $e->vector),
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        return $ranked->map(fn (array $row) => [
            'source_type' => $row['embedding']->source_type,
            'source_id' => $row['embedding']->source_id,
            'snippet' => self::snippet($row['embedding']->text),
            'score' => round($row['score'], 4),
            'metadata' => $row['embedding']->metadata,
        ])->all();
    }

    /**
     * @return list<int>
     */
    private function teamIdsFor(User $actor, ?int $teamId): array
    {
        $userTeamIds = $this->listTeamIds->execute($actor);

        if ($teamId === null) {
            return $userTeamIds;
        }

        return in_array($teamId, $userTeamIds, true) ? [$teamId] : [];
    }

    private function hasOwnMessages(User $actor): bool
    {
        return $actor->id > 0;
    }

    /**
     * Cosine similarity over two equal-length vectors. Defensive: returns 0
     * when shapes mismatch or either vector is zero — the search still
     * surfaces results, just won't crash on a stale row.
     *
     * @param  list<float>  $a
     * @param  list<float>  $b
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        $len = min(count($a), count($b));
        if ($len === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        for ($i = 0; $i < $len; $i++) {
            $av = (float) $a[$i];
            $bv = (float) $b[$i];
            $dot += $av * $bv;
            $normA += $av * $av;
            $normB += $bv * $bv;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private static function snippet(string $text, int $max = 240): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max).'…';
    }
}
