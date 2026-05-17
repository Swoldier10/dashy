<?php

namespace App\Domains\Search\Actions;

use App\Domains\Search\Models\Embedding;
use Illuminate\Database\Eloquent\Collection;

class FindEmbeddingsByScopeAction
{
    /**
     * Loads the candidate set for a semantic search. The scoping is coarse
     * on purpose: a SQL WHERE narrows the search to a single team's content
     * (or, for messages, to the actor's own messages), and the in-PHP cosine
     * ranking decides the rest. This keeps the index simple while still
     * preventing cross-team leakage.
     *
     * @param  array{source_types?: list<string>, team_ids?: list<int>, message_user_id?: int}  $filters
     * @return Collection<int, Embedding>
     */
    public function execute(array $filters = [], int $limit = 200): Collection
    {
        $query = Embedding::query();

        if (! empty($filters['source_types'])) {
            $query->whereIn('source_type', $filters['source_types']);
        }

        if (! empty($filters['team_ids'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereIn('team_id', $filters['team_ids']);
                if (isset($filters['message_user_id'])) {
                    // Messages don't have a team scope — fall back to the actor's
                    // own messages so a user can recall their own chat history.
                    $q->orWhere(function ($inner) use ($filters) {
                        $inner->where('source_type', 'message')
                            ->whereNull('team_id')
                            ->whereJsonContains('metadata->user_id', $filters['message_user_id']);
                    });
                }
            });
        } elseif (isset($filters['message_user_id'])) {
            $query->where('source_type', 'message')
                ->whereJsonContains('metadata->user_id', $filters['message_user_id']);
        }

        return $query->limit($limit)->get();
    }
}
