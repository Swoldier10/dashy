<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\ListTimeEntriesForTaskAction;
use App\Domains\TimeTracking\Actions\SumDurationForTaskAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

final class ListTaskTimeEntriesService
{
    public function __construct(
        private ListTimeEntriesForTaskAction $list,
        private SumDurationForTaskAction $sum,
    ) {}

    /**
     * @return array{entries: Collection<int, TimeEntry>, total_seconds: int}
     */
    public function execute(User $actor, Task $task): array
    {
        Gate::forUser($actor)->authorize('viewAny', [TimeEntry::class, $task]);

        return [
            'entries' => $this->list->execute($task),
            'total_seconds' => $this->sum->execute($task),
        ];
    }
}
