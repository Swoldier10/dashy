<?php

namespace App\Domains\Projects\Observers;

use App\Domains\Projects\Events\ProjectContentChanged;
use App\Domains\Projects\Models\Project;

/**
 * Emits a domain event when a project's searchable content changes, so Search
 * keeps its index in sync without observing the Projects model directly.
 */
final class ProjectSearchObserver
{
    public function saved(Project $project): void
    {
        ProjectContentChanged::dispatch((int) $project->getKey(), false);
    }

    public function deleted(Project $project): void
    {
        ProjectContentChanged::dispatch((int) $project->getKey(), true);
    }
}
