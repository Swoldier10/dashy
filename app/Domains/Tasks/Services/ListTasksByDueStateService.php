<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\ListTasksByDueStateAction;
use App\Domains\Tasks\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cross-user read for the notifications scheduler — no acting user, no
 * authorization scope. Only safe for internal callers (console commands,
 * queued jobs); never expose to the UI or AI tool surface.
 */
final class ListTasksByDueStateService
{
    public function __construct(
        private ListTasksByDueStateAction $list,
    ) {}

    /**
     * Tasks due within [$now, $now + 24h], not done/closed/archived,
     * with assignees and project eager-loaded.
     *
     * @return Collection<int, Task>
     */
    public function dueSoon(CarbonImmutable $now): Collection
    {
        return $this->list->execute('due_soon', $now, $now->addDay());
    }

    /**
     * Tasks whose due date has passed, not done/closed/archived.
     *
     * @return Collection<int, Task>
     */
    public function overdue(CarbonImmutable $now): Collection
    {
        return $this->list->execute('overdue', $now, $now);
    }
}
