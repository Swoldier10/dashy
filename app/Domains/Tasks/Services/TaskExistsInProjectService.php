<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\TaskExistsInProjectAction;

/**
 * Cheap "does this task belong to this project?" check used by the task
 * deep-link in the Volt pages to decide whether to hydrate the drawer on
 * initial mount. No actor auth — it's a boolean read with no exposure.
 */
final class TaskExistsInProjectService
{
    public function __construct(
        private TaskExistsInProjectAction $exists,
    ) {}

    public function execute(int $taskId, int $projectId): bool
    {
        return $this->exists->execute($taskId, $projectId);
    }
}
