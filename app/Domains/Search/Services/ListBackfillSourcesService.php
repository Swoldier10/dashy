<?php

namespace App\Domains\Search\Services;

use App\Domains\Search\Actions\ListBackfillSourceIdsAction;
use Generator;

/**
 * Yields (source_type, source_id) pairs for every row the
 * dashy:embed-backfill command needs to re-embed.
 */
final class ListBackfillSourcesService
{
    private const SCOPES = ['tasks', 'projects', 'messages', 'all'];

    public function __construct(
        private ListBackfillSourceIdsAction $listIds,
    ) {}

    /**
     * @return Generator<int, array{0: string, 1: int}>
     */
    public function execute(string $scope): Generator
    {
        if (! in_array($scope, self::SCOPES, true)) {
            throw new \InvalidArgumentException("Unknown scope [{$scope}].");
        }

        $types = match ($scope) {
            'tasks' => ['task'],
            'projects' => ['project'],
            'messages' => ['message'],
            'all' => ['task', 'project', 'message'],
        };

        foreach ($types as $type) {
            foreach ($this->listIds->execute($type) as $id) {
                yield [$type, $id];
            }
        }
    }

    public static function isValidScope(string $scope): bool
    {
        return in_array($scope, self::SCOPES, true);
    }

    /**
     * @return array<int, string>
     */
    public static function validScopes(): array
    {
        return self::SCOPES;
    }
}
