<?php

namespace App\Domains\Search\Actions;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use Generator;
use RuntimeException;

class ListBackfillSourceIdsAction
{
    /**
     * Stream every ID that needs (re-)embedding for a given source type.
     * Used by the embed-backfill command; chunked at 200 to keep memory flat.
     *
     * @return Generator<int, int>
     */
    public function execute(string $sourceType): Generator
    {
        $query = match ($sourceType) {
            'task' => Task::query(),
            'project' => Project::query(),
            'message' => Message::query()->whereNull('tool_call')->where('is_summary', false),
            default => throw new RuntimeException("Unknown source type [{$sourceType}]."),
        };

        foreach ($query->select('id')->lazyById(200) as $row) {
            yield (int) $row->id;
        }
    }
}
