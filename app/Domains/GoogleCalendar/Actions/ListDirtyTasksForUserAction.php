<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class ListDirtyTasksForUserAction
{
    /**
     * Returns tasks where the connection's user is an assignee (or the creator
     * when no assignees exist), the task has a future start_date, and the
     * local row is newer than the link's last_synced_at (or unlinked).
     *
     * @return Collection<int, Task>
     */
    public function execute(GoogleCalendarConnection $connection): Collection
    {
        $userId = $connection->user_id;

        return Task::query()
            ->select('tasks.*')
            ->leftJoin('google_calendar_links', function ($join) use ($connection) {
                $join->on('google_calendar_links.syncable_id', '=', 'tasks.id')
                    ->where('google_calendar_links.syncable_type', '=', Task::class)
                    ->where('google_calendar_links.connection_id', '=', $connection->id);
            })
            ->where(function ($q) use ($userId) {
                $q->whereIn('tasks.id', function ($sub) use ($userId) {
                    $sub->select('task_id')->from('task_user')->where('user_id', $userId);
                })->orWhere(function ($qq) use ($userId) {
                    $qq->where('tasks.created_by_user_id', $userId)
                        ->whereNotIn('tasks.id', function ($sub) {
                            $sub->select('task_id')->from('task_user');
                        });
                });
            })
            ->whereNotNull('tasks.start_date')
            ->where('tasks.start_date', '>=', now())
            ->where('tasks.is_archived', false)
            ->where(function ($q) {
                $q->whereNull('google_calendar_links.id')
                    ->orWhereColumn('tasks.updated_at', '>', 'google_calendar_links.last_synced_at');
            })
            ->orderBy('tasks.start_date')
            ->get();
    }
}
