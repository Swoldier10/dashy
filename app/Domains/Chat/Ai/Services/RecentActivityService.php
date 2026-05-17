<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Chat\Services\ListRecentChatsService;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListUserOpenTasksService;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\ListRecentTimeEntriesForUserService;
use App\Models\User;

/**
 * Cross-domain coordinator: composes recent activity across the user's tasks,
 * time entries, and chats. Lives under Chat/Ai because the only consumer is
 * the AI chat's `recent_activity` tool; the data sources are accessed only
 * through their public domain services (per CLAUDE.md rule 1).
 */
final class RecentActivityService
{
    public function __construct(
        private ListUserOpenTasksService $listOpenTasks,
        private ListRecentTimeEntriesForUserService $listRecentTime,
        private ListRecentChatsService $listRecentChats,
    ) {}

    /**
     * @return array{open_tasks: array<int, array<string, mixed>>, recent_time_entries: array<int, array<string, mixed>>, recent_chats: array<int, array<string, mixed>>}
     */
    public function execute(User $actor, int $perBucket = 10): array
    {
        $perBucket = max(1, min(50, $perBucket));

        $tasks = $this->listOpenTasks->execute($actor, onlyMine: true, limit: $perBucket);
        $time = $this->listRecentTime->execute($actor, limit: $perBucket);
        $chats = $this->listRecentChats->execute($actor, limit: $perBucket);

        return [
            'open_tasks' => $tasks->map(fn (Task $t) => [
                'id' => $t->id,
                'name' => (string) $t->name,
                'project_name' => $t->project?->name,
                'end_date' => $t->end_date?->toDateString(),
            ])->values()->all(),
            'recent_time_entries' => $time->map(fn (TimeEntry $e) => [
                'id' => $e->id,
                'task_name' => $e->task?->name,
                'started_at' => $e->started_at?->toIso8601String(),
                'ended_at' => $e->ended_at?->toIso8601String(),
                'duration_seconds' => (int) ($e->duration_seconds ?? 0),
            ])->values()->all(),
            'recent_chats' => $chats->map(fn ($c) => [
                'id' => $c->id,
                'title' => (string) ($c->title ?? ''),
                'updated_at' => $c->updated_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
