<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Search\Services\EmbedTextService;
use App\Domains\Search\Services\SemanticSearchService;
use App\Models\User;

/**
 * AUTO-READ. Semantic search across the user's tasks, projects, and chat
 * messages. Backed by the chat_embeddings index — populated by save
 * observers (live) and the dashy:embed-backfill console command (history).
 * When the OPENAI_API_KEY is missing, the tool reports unavailable so the
 * model gracefully falls back to explicit list/get tools.
 */
final class SemanticSearchTool implements AiTool
{
    public function __construct(
        private SemanticSearchService $search,
        private EmbedTextService $embed,
    ) {}

    public function name(): string
    {
        return 'semantic_search';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Semantic search across the user\'s tasks, projects, and chat history. Useful when the '
            .'user refers to something without giving an id and the answer could be in older content '
            .'(e.g. "what did we decide about onboarding emails?"). Returns the top matches with a '
            .'short snippet, the source type, and the source id you can hand to get_task / '
            .'get_project_overview to fetch full detail.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 500],
                'scope' => [
                    'type' => 'string',
                    'enum' => ['tasks', 'projects', 'messages', 'all'],
                ],
                'team_id' => ['type' => 'integer'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
            ],
            'required' => ['query'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $query = $arguments['query'] ?? null;
        if (! is_string($query) || trim($query) === '') {
            return AiToolValidationResult::fail(['query is required.']);
        }

        $normalized = ['query' => trim($query)];

        if (isset($arguments['scope']) && is_string($arguments['scope'])) {
            $normalized['scope'] = $arguments['scope'];
        }
        if (isset($arguments['team_id']) && is_int($arguments['team_id'])) {
            $normalized['team_id'] = $arguments['team_id'];
        }
        if (isset($arguments['limit']) && is_int($arguments['limit'])) {
            $normalized['limit'] = max(1, min(20, $arguments['limit']));
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        if (! $this->embed->isConfigured()) {
            return [
                'available' => false,
                'reason' => 'OPENAI_API_KEY is not configured; semantic search is unavailable.',
                'results' => [],
            ];
        }

        $scope = $arguments['scope'] ?? 'all';
        $scopes = $scope === 'all' ? null : [rtrim((string) $scope, 's')]; // tasks -> task

        $hits = $this->search->execute(
            $user,
            (string) $arguments['query'],
            $scopes,
            $arguments['team_id'] ?? null,
            (int) ($arguments['limit'] ?? 8),
        );

        return [
            'available' => true,
            'count' => count($hits),
            'results' => $hits,
        ];
    }
}
