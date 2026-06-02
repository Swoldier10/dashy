<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Turns a stored tool_call payload into the view the preview card renders.
 *
 * Bespoke cards (create_task / create_project / create_event / ask_user_choice
 * / plan) are owned by the tools themselves via {@see PresentsToolCard}; this
 * service delegates to them. The remaining tools fall into a few shared
 * presentation *strategies* — auto-read pills, compact-write summaries, bulk
 * lists, destructive confirmations — which live here because the logic is
 * genuinely shared across many tools. Human labels come from the injected
 * {@see ToolCardLabelResolver}.
 */
final class AiToolCardPresenter
{
    public function __construct(
        private AiToolRegistry $registry,
        private ToolCardLabelResolver $labels,
    ) {}

    /**
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    public function present(array $toolCall, User $user): array
    {
        $name = (string) ($toolCall['name'] ?? '');

        $tool = $this->registry->find($name);
        if ($tool instanceof PresentsToolCard) {
            return $tool->presentCard($toolCall, $user);
        }

        return match (true) {
            $this->isAutoReadName($name) => $this->presentAutoRead($toolCall),
            $this->isDestructiveName($name) => $this->presentDestructive($toolCall, $user),
            $this->isBulkWriteName($name) => $this->presentBulkWrite($toolCall, $user),
            $this->isCompactWriteName($name) => $this->presentCompactWrite($toolCall, $user),
            default => $toolCall,
        };
    }

    /**
     * Destructive single-target writes (delete_task, delete_project, delete_event).
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentDestructive(array $toolCall, User $user): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        return [
            'name' => $name,
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'mode' => 'compact_write',
            'title' => match ($name) {
                'delete_task' => __('Delete task'),
                'delete_project' => __('Delete project'),
                'delete_event' => __('Delete event'),
                default => __('Delete'),
            },
            'summary' => match ($name) {
                'delete_task' => __('Permanently delete :task. This cannot be undone.', [
                    'task' => isset($args['task_id']) ? $this->labels->taskLabel($user, (int) $args['task_id']) : '?',
                ]),
                'delete_project' => __('Permanently delete :project and every task in it.', [
                    'project' => isset($args['project_id']) ? $this->labels->projectLabel($user, (int) $args['project_id']) : '?',
                ]),
                'delete_event' => __('Permanently delete :event. Recurring events lose the entire series.', [
                    'event' => isset($args['event_id']) ? $this->labels->eventLabel($user, (int) $args['event_id']) : '?',
                ]),
                default => __('Cannot be undone.'),
            },
            'icon' => 'trash',
            'destructive' => true,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'result' => is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [],
        ];
    }

    /**
     * Bulk write tools (bulk_move_tasks_to_status, bulk_assign_tasks,
     * bulk_archive_tasks, bulk_delete_tasks).
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentBulkWrite(array $toolCall, User $user): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        $taskIds = array_values(array_filter(array_map(
            fn ($v) => is_int($v) ? $v : null,
            (array) ($args['task_ids'] ?? []),
        ), fn ($v) => $v !== null));

        $rows = array_map(fn (int $id) => [
            'task_id' => $id,
            'label' => $this->labels->taskLabel($user, $id),
        ], $taskIds);

        return [
            'name' => $name,
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'mode' => 'bulk_write',
            'title' => match ($name) {
                'bulk_move_tasks_to_status' => __('Move :n tasks', ['n' => count($rows)]),
                'bulk_assign_tasks' => __('Assign :n tasks', ['n' => count($rows)]),
                'bulk_archive_tasks' => __('Archive :n tasks', ['n' => count($rows)]),
                'bulk_delete_tasks' => __('Delete :n tasks', ['n' => count($rows)]),
                default => __('Bulk apply'),
            },
            'subtitle' => match ($name) {
                'bulk_move_tasks_to_status' => __('Target status: :status', [
                    'status' => isset($args['target_status_id']) ? $this->labels->statusLabel($user, (int) $args['target_status_id']) : '?',
                ]),
                'bulk_assign_tasks' => __('Assignee: :user', [
                    'user' => isset($args['user_id']) ? $this->labels->userLabel((int) $args['user_id']) : '?',
                ]),
                'bulk_delete_tasks' => __('Cannot be undone.'),
                default => '',
            },
            'icon' => match ($name) {
                'bulk_move_tasks_to_status' => 'arrow-right-circle',
                'bulk_assign_tasks' => 'user-plus',
                'bulk_archive_tasks' => 'archive-box',
                'bulk_delete_tasks' => 'trash',
                default => 'queue-list',
            },
            'destructive' => $name === 'bulk_delete_tasks',
            'rows' => $rows,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'result' => is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [],
        ];
    }

    private function isDestructiveName(string $name): bool
    {
        return in_array($name, ['delete_task', 'delete_project', 'delete_event'], true);
    }

    private function isBulkWriteName(string $name): bool
    {
        return in_array($name, [
            'bulk_move_tasks_to_status',
            'bulk_assign_tasks',
            'bulk_archive_tasks',
            'bulk_delete_tasks',
        ], true);
    }

    /**
     * Single-target write tools (update_task_*, move_task_to_status, assign_task,
     * archive_task, start_timer, etc.) share one generic compact card.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentCompactWrite(array $toolCall, User $user): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        return [
            'name' => $name,
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'mode' => 'compact_write',
            'title' => $this->compactWriteTitle($name),
            'summary' => $this->compactWriteSummary($name, $args, $user),
            'icon' => $this->compactWriteIcon($name),
            'destructive' => false,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'result' => is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [],
        ];
    }

    private function compactWriteTitle(string $name): string
    {
        return match ($name) {
            'update_task_name' => __('Rename task'),
            'update_task_description' => __('Update task description'),
            'update_task_priority' => __('Change priority'),
            'update_task_dates' => __('Update task dates'),
            'move_task_to_status' => __('Move task'),
            'attach_image_to_task' => __('Attach image to task'),
            'assign_task' => __('Assign task'),
            'unassign_task' => __('Unassign'),
            'archive_task' => __('Archive task'),
            'unarchive_task' => __('Unarchive task'),
            'start_timer' => __('Start timer'),
            'stop_timer' => __('Stop timer'),
            'log_manual_time' => __('Log time'),
            'rename_project' => __('Rename project'),
            'add_project_status' => __('Add status'),
            'rename_project_status' => __('Rename status'),
            'delete_project_status' => __('Delete status'),
            'update_event' => __('Update event'),
            default => __('Apply'),
        };
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function compactWriteSummary(string $name, array $args, User $user): string
    {
        $taskLabel = isset($args['task_id']) ? $this->labels->taskLabel($user, (int) $args['task_id']) : null;
        $statusLabel = isset($args['target_status_id']) ? $this->labels->statusLabel($user, (int) $args['target_status_id']) : null;
        $userLabel = isset($args['user_id']) ? $this->labels->userLabel((int) $args['user_id']) : null;
        $statusId = isset($args['project_status_id']) ? $this->labels->statusLabel($user, (int) $args['project_status_id']) : null;
        $projectLabel = isset($args['project_id']) ? $this->labels->projectLabel($user, (int) $args['project_id']) : null;

        return match ($name) {
            'update_task_name' => __(':task → :new', [
                'task' => $taskLabel ?? '?',
                'new' => Str::limit((string) ($args['name'] ?? ''), 60),
            ]),
            'update_task_description' => __('Update description of :task', ['task' => $taskLabel ?? '?']),
            'update_task_priority' => __('Set priority of :task to :priority', [
                'task' => $taskLabel ?? '?',
                'priority' => (string) ($args['priority'] ?? '?'),
            ]),
            'update_task_dates' => __(':task: :start → :end', [
                'task' => $taskLabel ?? '?',
                'start' => $args['start_date'] ?? '—',
                'end' => $args['end_date'] ?? '—',
            ]),
            'move_task_to_status' => __('Move :task → :status', [
                'task' => $taskLabel ?? '?',
                'status' => $statusLabel ?? '?',
            ]),
            'attach_image_to_task' => __('Attach image(s) to :task', ['task' => $taskLabel ?? '?']),
            'assign_task' => __('Add :user to :task', [
                'user' => $userLabel ?? '?',
                'task' => $taskLabel ?? '?',
            ]),
            'unassign_task' => __('Remove :user from :task', [
                'user' => $userLabel ?? '?',
                'task' => $taskLabel ?? '?',
            ]),
            'archive_task' => __('Archive :task', ['task' => $taskLabel ?? '?']),
            'unarchive_task' => __('Unarchive :task', ['task' => $taskLabel ?? '?']),
            'start_timer' => __('Start timer on :task', ['task' => $taskLabel ?? '?']),
            'stop_timer' => __('Stop the running timer'),
            'log_manual_time' => __('Log :duration on :task', [
                'duration' => (string) ($args['duration'] ?? __('manual entry')),
                'task' => $taskLabel ?? '?',
            ]),
            'rename_project' => __(':project → :new', [
                'project' => $projectLabel ?? '?',
                'new' => Str::limit((string) ($args['name'] ?? ''), 60),
            ]),
            'add_project_status' => __('Add status ":name" to :project', [
                'name' => Str::limit((string) ($args['name'] ?? ''), 30),
                'project' => $projectLabel ?? '?',
            ]),
            'rename_project_status' => __('Rename status :status → :new', [
                'status' => $statusId ?? '?',
                'new' => Str::limit((string) ($args['name'] ?? ''), 30),
            ]),
            'delete_project_status' => __('Delete status :status', ['status' => $statusId ?? '?']),
            'update_event' => $this->updateEventSummary($args, $user),
            'remember' => __('Remember (:scope): :fact', [
                'scope' => (string) ($args['scope'] ?? 'user'),
                'fact' => Str::limit((string) ($args['fact'] ?? ''), 120),
            ]),
            'forget' => __('Forget :key (:scope)', [
                'scope' => (string) ($args['scope'] ?? 'user'),
                'key' => (string) ($args['key'] ?? ''),
            ]),
            default => '',
        };
    }

    private function compactWriteIcon(string $name): string
    {
        return match ($name) {
            'update_task_name', 'update_task_description' => 'pencil-square',
            'update_task_priority' => 'flag',
            'update_task_dates' => 'calendar-days',
            'move_task_to_status' => 'arrow-right-circle',
            'attach_image_to_task' => 'paper-clip',
            'assign_task', 'unassign_task' => 'user-plus',
            'archive_task', 'unarchive_task' => 'archive-box',
            'start_timer', 'stop_timer', 'log_manual_time' => 'clock',
            'rename_project', 'add_project_status', 'rename_project_status', 'delete_project_status' => 'folder',
            'update_event' => 'calendar-days',
            'remember', 'forget' => 'bookmark',
            default => 'pencil-square',
        };
    }

    private function isCompactWriteName(string $name): bool
    {
        return in_array($name, [
            'update_task_name',
            'update_task_description',
            'update_task_priority',
            'update_task_dates',
            'move_task_to_status',
            'attach_image_to_task',
            'assign_task',
            'unassign_task',
            'archive_task',
            'unarchive_task',
            'start_timer',
            'stop_timer',
            'log_manual_time',
            'rename_project',
            'add_project_status',
            'rename_project_status',
            'delete_project_status',
            'update_event',
            'remember',
            'forget',
        ], true);
    }

    /**
     * Read-only "ghost pill" presentation for every auto_read tool.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentAutoRead(array $toolCall): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $result = is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [];

        return [
            'name' => $name,
            'status' => (string) ($toolCall['status'] ?? 'executed'),
            'mode' => 'auto_read',
            'label' => $this->autoReadLabel($name, $result),
            'icon' => $this->autoReadIcon($name),
            'count' => isset($result['count']) ? (int) $result['count'] : null,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function autoReadLabel(string $toolName, array $result): string
    {
        $count = isset($result['count']) ? (int) $result['count'] : null;
        $error = is_string($result['error'] ?? null) ? (string) $result['error'] : null;

        if ($error !== null) {
            return $error;
        }

        return match ($toolName) {
            'list_tasks' => __(':n task(s) listed', ['n' => $count ?? 0]),
            'get_task' => __('Task fetched'),
            'list_my_open_tasks' => __(':n open task(s)', ['n' => $count ?? 0]),
            'list_overdue_tasks' => __(':n overdue task(s)', ['n' => $count ?? 0]),
            'list_projects' => __(':n project(s)', ['n' => $count ?? 0]),
            'get_project_overview' => __('Project overview fetched'),
            'list_team_members' => __(':n team member(s)', ['n' => $count ?? 0]),
            'who_is_working_on' => __(':n active timer(s)', ['n' => $count ?? 0]),
            'recent_activity' => __('Recent activity fetched'),
            'find_user_by_email' => isset($result['found']) && $result['found'] === true
                ? __('User found')
                : __('User not found'),
            'get_time_summary' => __('Time summary fetched'),
            'semantic_search' => __('Semantic search'),
            'list_memories' => __(':n memories', ['n' => $count ?? 0]),
            'list_events' => __(':n event(s)', ['n' => $count ?? 0]),
            default => __('Lookup'),
        };
    }

    private function autoReadIcon(string $toolName): string
    {
        return match ($toolName) {
            'list_tasks', 'get_task', 'list_my_open_tasks', 'list_overdue_tasks' => 'clipboard-document-check',
            'list_projects', 'get_project_overview' => 'folder',
            'list_team_members', 'find_user_by_email' => 'users',
            'who_is_working_on' => 'clock',
            'recent_activity' => 'sparkles',
            'get_time_summary' => 'chart-bar',
            'semantic_search' => 'magnifying-glass',
            'list_memories' => 'bookmark',
            'list_events' => 'calendar-days',
            default => 'magnifying-glass',
        };
    }

    private function isAutoReadName(string $name): bool
    {
        return in_array($name, [
            'list_tasks',
            'get_task',
            'list_my_open_tasks',
            'list_overdue_tasks',
            'list_projects',
            'get_project_overview',
            'list_team_members',
            'who_is_working_on',
            'recent_activity',
            'find_user_by_email',
            'get_time_summary',
            'semantic_search',
            'list_memories',
            'list_events',
        ], true);
    }

    /**
     * Compact-write summary for update_event — a short line listing changed fields.
     *
     * @param  array<string, mixed>  $args
     */
    private function updateEventSummary(array $args, User $user): string
    {
        $eventLabel = isset($args['event_id']) ? $this->labels->eventLabel($user, (int) $args['event_id']) : '?';

        $changes = [];
        if (isset($args['title'])) {
            $changes[] = __('Title → :v', ['v' => Str::limit((string) $args['title'], 40)]);
        }
        if (isset($args['start_at'])) {
            $changes[] = __('Start → :v', ['v' => (string) $args['start_at']]);
        }
        if (isset($args['end_at'])) {
            $changes[] = __('End → :v', ['v' => (string) $args['end_at']]);
        }
        if (array_key_exists('description', $args)) {
            $changes[] = __('Description');
        }
        if (isset($args['is_all_day'])) {
            $changes[] = __('All-day → :v', ['v' => $args['is_all_day'] ? __('yes') : __('no')]);
        }
        if (isset($args['color'])) {
            $changes[] = __('Color → :v', ['v' => (string) $args['color']]);
        }
        if (array_key_exists('location', $args)) {
            $changes[] = __('Location');
        }
        if (isset($args['recurrence_freq'])) {
            $changes[] = __('Repeat → :v', ['v' => (string) $args['recurrence_freq']]);
        }
        if (array_key_exists('recurrence_until', $args)) {
            $changes[] = __('Repeat until → :v', ['v' => (string) ($args['recurrence_until'] ?? '—')]);
        }

        if ($changes === []) {
            return __('Update :event', ['event' => $eventLabel]);
        }

        return __(':event: :changes', [
            'event' => $eventLabel,
            'changes' => implode(', ', $changes),
        ]);
    }
}
