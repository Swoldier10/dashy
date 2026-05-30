<?php

namespace App\Domains\Tasks\Observers;

use App\Domains\Tasks\Events\TaskContentChanged;
use App\Domains\Tasks\Models\Task;

/**
 * Emits a domain event whenever a task's searchable content changes, so the
 * Search domain (and any future listener, e.g. notifications) can react
 * without observing the Tasks model itself.
 */
final class TaskSearchObserver
{
    public function saved(Task $task): void
    {
        TaskContentChanged::dispatch((int) $task->getKey(), false);
    }

    public function deleted(Task $task): void
    {
        TaskContentChanged::dispatch((int) $task->getKey(), true);
    }
}
