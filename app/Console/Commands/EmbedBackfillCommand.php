<?php

namespace App\Console\Commands;

use App\Domains\Search\Jobs\EmbedSourceJob;
use App\Domains\Search\Services\ListBackfillSourcesService;
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

    public function handle(ListBackfillSourcesService $sources): int
    {
        $scope = (string) $this->option('scope');
        if (! ListBackfillSourcesService::isValidScope($scope)) {
            $this->error('Invalid --scope; must be one of: '.implode(', ', ListBackfillSourcesService::validScopes()));

            return self::INVALID;
        }

        $totals = ['task' => 0, 'project' => 0, 'message' => 0];

        foreach ($sources->execute($scope) as [$type, $id]) {
            EmbedSourceJob::dispatch($type, $id);
            $totals[$type]++;
        }

        $this->info(sprintf(
            'Queued %d task embed jobs, %d project, %d message.',
            $totals['task'],
            $totals['project'],
            $totals['message'],
        ));

        return self::SUCCESS;
    }
}
