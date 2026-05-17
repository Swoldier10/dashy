<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListTasksForUserAction
{
    /**
     * Tasks across every team the user belongs to. Filters keyed by:
     *   - assignee_user_id: only tasks where this user is assigned
     *   - statuses_not_in_category: list of ProjectStatusCategory values to exclude
     *   - overdue_only: end_date strictly before today
     *   - include_archived: include archived tasks (defaults false)
     *   - range_from / range_to: only tasks whose schedule overlaps this date
     *     window. Tasks with no start_date are excluded. Window semantics:
     *     start_date <= range_to AND (end_date IS NULL OR end_date >= range_from).
     *
     * @param  array{assignee_user_id?: int, statuses_not_in_category?: list<string>, overdue_only?: bool, include_archived?: bool, limit?: int, range_from?: string, range_to?: string}  $filters
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $filters = []): Collection
    {
        $query = Task::query()
            ->whereHas('project.team.members', fn ($q) => $q->whereKey($actor->id))
            ->with(['project', 'status', 'assignees']);

        if (! ($filters['include_archived'] ?? false)) {
            $query->where('is_archived', false);
        }

        if (isset($filters['assignee_user_id'])) {
            $query->whereHas('assignees', fn ($q) => $q->whereKey($filters['assignee_user_id']));
        }

        if (! empty($filters['statuses_not_in_category'])) {
            $excludeCategories = array_filter(
                array_map(
                    fn ($v) => ProjectStatusCategory::tryFrom((string) $v),
                    $filters['statuses_not_in_category'],
                ),
            );
            if ($excludeCategories !== []) {
                $values = array_map(fn (ProjectStatusCategory $c) => $c->value, $excludeCategories);
                $query->whereHas('status', fn ($q) => $q->whereNotIn('category', $values));
            }
        }

        if (! empty($filters['overdue_only'])) {
            $query->whereNotNull('end_date')->whereDate('end_date', '<', now()->toDateString());
        }

        if (isset($filters['range_from']) || isset($filters['range_to'])) {
            $query->whereNotNull('start_date');

            if (isset($filters['range_to'])) {
                $query->whereDate('start_date', '<=', $filters['range_to']);
            }

            if (isset($filters['range_from'])) {
                $query->where(function ($q) use ($filters) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $filters['range_from']);
                });
            }
        }

        $query->orderBy('end_date')->orderBy('id');

        if (isset($filters['limit'])) {
            $query->limit(max(1, min(200, (int) $filters['limit'])));
        }

        return $query->get();
    }
}
