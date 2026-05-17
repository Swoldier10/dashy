<?php

namespace App\Console\Commands;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Tasks\Models\Task;
use Illuminate\Console\Command;

/**
 * Backfills the semantic-search index by dispatching one EmbedSourceJob per
 * task / project / message. Safe to re-run — the job upserts on
 * (source_type, source_id), so existing rows are refreshed in place rather
 * than duplicated.
 */
class EmbedBackfillCommand extends Command
{
    protected $signature = 'dashy:embed-backfill {--scope=all : tasks|projects|messages|all}';

    protected $description = 'Queue EmbedSourceJob for every task / project / message so semantic_search can find them.';

    public function handle(): int
    {
        $scope = (string) $this->option('scope');
        $valid = ['tasks', 'projects', 'messages', 'all'];
        if (! in_array($scope, $valid, true)) {
            $this->error("Invalid --scope; must be one of: ".implode(', ', $valid));

            return self::INVALID;
        }

        $totals = ['tasks' => 0, 'projects' => 0, 'messages' => 0];

        if ($scope === 'tasks' || $scope === 'all') {
            $totals['tasks'] = $this->backfill('task', Task::query());
        }
        if ($scope === 'projects' || $scope === 'all') {
            $totals['projects'] = $this->backfill('project', Project::query());
        }
        if ($scope === 'messages' || $scope === 'all') {
            // Only embed real text messages — skip tool-call rows and summary
            // placeholders that EmbedSourceJob would no-op anyway.
            $totals['messages'] = $this->backfill(
                'message',
                Message::query()->whereNull('tool_call')->where('is_summary', false),
            );
        }

        $this->info(sprintf(
            'Queued %d task embed jobs, %d project, %d message.',
            $totals['tasks'],
            $totals['projects'],
            $totals['messages'],
        ));

        return self::SUCCESS;
    }

    private function backfill(string $sourceType, $query): int
    {
        $count = 0;
        $query->select('id')->chunkById(200, function ($rows) use (&$count, $sourceType) {
            foreach ($rows as $row) {
                EmbedSourceJob::dispatch($sourceType, (int) $row->id);
                $count++;
            }
        });

        return $count;
    }
}
