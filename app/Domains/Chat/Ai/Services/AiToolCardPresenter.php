<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Auth\Actions\FindUsersByIdsAction;
use App\Domains\Calendar\Actions\FindEventAction;
use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Chat\Ai\Tools\CreateProjectTool;
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Domains\Projects\Actions\FindProjectWithTeamMembersAction;
use App\Domains\Projects\Actions\ListProjectStatusesForProjectAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Teams\Actions\FindTeamAction;
use App\Domains\Teams\Actions\ListTeamsForUserAction;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

final class AiToolCardPresenter
{
    public function __construct(
        private FindProjectAction $findProject,
        private FindProjectStatusAction $findStatus,
        private FindTaskAction $findTask,
        private FindUsersByIdsAction $findUsers,
        private FindTeamAction $findTeam,
        private FindProjectWithTeamMembersAction $findProjectWithMembers,
        private ListProjectStatusesForProjectAction $listProjectStatuses,
        private ListTeamsForUserAction $listTeamsForUser,
        private FindEventAction $findEvent,
    ) {}

    /**
     * Enrich a tool_call payload with human-readable labels for the preview
     * card, plus the lookup catalogues the editable inputs need
     * (available_teams / available_statuses / available_priorities /
     * available_assignees).
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    public function present(array $toolCall, User $user): array
    {
        $name = (string) ($toolCall['name'] ?? '');

        return match (true) {
            $name === 'create_task' => $this->presentCreateTask($toolCall),
            $name === 'create_project' => $this->presentCreateProject($toolCall, $user),
            $name === 'create_event' => $this->presentCreateEvent($toolCall),
            $name === 'ask_user_choice' => $this->presentAskUserChoice($toolCall),
            $name === 'plan' => $this->presentPlan($toolCall),
            $this->isAutoReadName($name) => $this->presentAutoRead($toolCall),
            $this->isDestructiveName($name) => $this->presentDestructive($toolCall),
            $this->isBulkWriteName($name) => $this->presentBulkWrite($toolCall),
            $this->isCompactWriteName($name) => $this->presentCompactWrite($toolCall),
            default => $toolCall,
        };
    }

    /**
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentPlan(array $toolCall): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $result = is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [];
        $status = (string) ($toolCall['status'] ?? 'executed');

        // Steps may be stored under `result.steps` (post-execution) or
        // `arguments.steps` (failed validation). Prefer result for fidelity.
        $steps = is_array($result['steps'] ?? null)
            ? array_values(array_filter(array_map('strval', $result['steps'])))
            : array_values(array_filter(array_map('strval', (array) ($args['steps'] ?? []))));

        return [
            'name' => 'plan',
            'status' => $status,
            'mode' => 'structural_auto',
            'steps' => $steps,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
        ];
    }

    /**
     * Destructive single-target writes (delete_task, delete_project). Reuses
     * the compact-write partial but with a red Apply button and a strong
     * warning line.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentDestructive(array $toolCall): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        return [
            'name' => $name,
            'status' => $status,
            'mode' => 'compact_write',
            'title' => match ($name) {
                'delete_task' => __('Delete task'),
                'delete_project' => __('Delete project'),
                'delete_event' => __('Delete event'),
                default => __('Delete'),
            },
            'summary' => match ($name) {
                'delete_task' => __('Permanently delete :task. This cannot be undone.', [
                    'task' => isset($args['task_id']) ? $this->taskLabel((int) $args['task_id']) : '?',
                ]),
                'delete_project' => __('Permanently delete :project and every task in it.', [
                    'project' => isset($args['project_id']) ? $this->projectLabel((int) $args['project_id']) : '?',
                ]),
                'delete_event' => __('Permanently delete :event. Recurring events lose the entire series.', [
                    'event' => isset($args['event_id']) ? $this->eventLabel((int) $args['event_id']) : '?',
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
     * bulk_archive_tasks, bulk_delete_tasks). Resolves every task name so the
     * user reviews exactly what's about to change.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentBulkWrite(array $toolCall): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');
        $isDestructive = $name === 'bulk_delete_tasks';

        $taskIds = array_values(array_filter(array_map(
            fn ($v) => is_int($v) ? $v : null,
            (array) ($args['task_ids'] ?? []),
        ), fn ($v) => $v !== null));

        $rows = array_map(fn (int $id) => [
            'task_id' => $id,
            'label' => $this->taskLabel($id) ?? ('#'.$id),
        ], $taskIds);

        return [
            'name' => $name,
            'status' => $status,
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
                    'status' => isset($args['target_status_id']) ? $this->statusLabel((int) $args['target_status_id']) : '?',
                ]),
                'bulk_assign_tasks' => __('Assignee: :user', [
                    'user' => isset($args['user_id']) ? $this->userLabel((int) $args['user_id']) : '?',
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
            'destructive' => $isDestructive,
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
     * Single-target write tools (update_task_*, move_task_to_status,
     * assign_task, archive_task, start_timer, etc.) all render through the
     * same generic compact card. The presenter builds a short human-readable
     * summary line per tool name; everything else (Apply/Discard buttons,
     * status badges, error list) is shared in the partial.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentCompactWrite(array $toolCall): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        return [
            'name' => $name,
            'status' => $status,
            'mode' => 'compact_write',
            'title' => $this->compactWriteTitle($name),
            'summary' => $this->compactWriteSummary($name, $args),
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
    private function compactWriteSummary(string $name, array $args): string
    {
        $taskLabel = isset($args['task_id']) ? $this->taskLabel((int) $args['task_id']) : null;
        $statusLabel = isset($args['target_status_id']) ? $this->statusLabel((int) $args['target_status_id']) : null;
        $userLabel = isset($args['user_id']) ? $this->userLabel((int) $args['user_id']) : null;
        $statusId = isset($args['project_status_id']) ? $this->statusLabel((int) $args['project_status_id']) : null;
        $projectLabel = isset($args['project_id']) ? $this->projectLabel((int) $args['project_id']) : null;

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
            'update_event' => $this->updateEventSummary($args),
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
            'assign_task', 'unassign_task' => 'user-plus',
            'archive_task', 'unarchive_task' => 'archive-box',
            'start_timer', 'stop_timer', 'log_manual_time' => 'clock',
            'rename_project', 'add_project_status', 'rename_project_status', 'delete_project_status' => 'folder',
            'update_event' => 'calendar-days',
            'remember', 'forget' => 'bookmark',
            default => 'pencil-square',
        };
    }

    private function taskLabel(int $id): ?string
    {
        try {
            $task = $this->findTask->execute($id);

            return '"'.Str::limit((string) $task->name, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    private function statusLabel(int $id): ?string
    {
        try {
            $status = $this->findStatus->execute($id);

            return '"'.Str::limit((string) $status->name, 30).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    private function projectLabel(int $id): ?string
    {
        try {
            $project = $this->findProject->execute($id);

            return '"'.Str::limit((string) $project->name, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    private function userLabel(int $id): ?string
    {
        $found = $this->findUsers->execute([$id])->first();

        return $found !== null ? (string) $found->name : '#'.$id;
    }

    private function isCompactWriteName(string $name): bool
    {
        return in_array($name, [
            'update_task_name',
            'update_task_description',
            'update_task_priority',
            'update_task_dates',
            'move_task_to_status',
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
     * Read-only "ghost pill" presentation for every auto_read tool. The UI
     * renders a single line summary (icon + verb + object) rather than the
     * full result payload — the data went straight to the LLM.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentAutoRead(array $toolCall): array
    {
        $name = (string) ($toolCall['name'] ?? '');
        $status = (string) ($toolCall['status'] ?? 'executed');
        $result = is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [];

        return [
            'name' => $name,
            'status' => $status,
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
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentCreateTask(array $toolCall): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        $view = [
            'name' => $toolCall['name'] ?? null,
            'status' => $status,
            'task_name' => (string) ($args['name'] ?? ''),
            'description' => (string) ($args['description'] ?? ''),
            'priority' => null,
            'project' => null,
            'task_status' => null,
            'start_date' => $args['start_date'] ?? null,
            'end_date' => $args['end_date'] ?? null,
            'assignees' => [],
            'images' => [],
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'created_task_id' => null,
            'assignee_user_ids' => array_values(array_map(
                'intval',
                (array) ($args['assignee_user_ids'] ?? []),
            )),
            'available_priorities' => array_map(
                static fn (TaskPriority $p) => [
                    'value' => $p->value,
                    'label' => $p->label(),
                    'color_var' => $p->colorVar(),
                ],
                TaskPriority::cases(),
            ),
            'available_statuses' => [],
            'available_assignees' => [],
        ];

        $images = (array) ($args['image_attachments'] ?? []);
        $view['images'] = array_values(array_filter(array_map(static function ($att): ?array {
            if (! is_array($att)) {
                return null;
            }
            $url = $att['url'] ?? null;
            if (! is_string($url) || $url === '') {
                return null;
            }

            return [
                'url' => $url,
                'name' => is_string($att['name'] ?? null) ? $att['name'] : null,
            ];
        }, $images)));

        $assigneeIds = array_map('intval', (array) ($args['assignee_user_ids'] ?? []));
        if ($assigneeIds !== []) {
            $view['assignees'] = $this->findUsers->execute($assigneeIds)
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                ->values()
                ->all();
        }

        $priorityValue = $args['priority'] ?? null;
        if (is_string($priorityValue)) {
            $priority = TaskPriority::tryFrom($priorityValue);
            if ($priority !== null) {
                $view['priority'] = [
                    'value' => $priority->value,
                    'label' => $priority->label(),
                    'color_var' => $priority->colorVar(),
                ];
            }
        }

        $projectId = $args['project_id'] ?? null;
        if (is_int($projectId) || (is_string($projectId) && ctype_digit($projectId))) {
            try {
                $project = $this->findProjectWithMembers->execute((int) $projectId);
                $view['project'] = [
                    'id' => $project->id,
                    'name' => $project->name,
                ];

                $view['available_statuses'] = $this->listProjectStatuses
                    ->execute($project)
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'category' => $s->category->value,
                        'color_var' => $s->category->colorVar(),
                    ])
                    ->values()
                    ->all();

                $view['available_assignees'] = $project->team->members
                    ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])
                    ->values()
                    ->all();
            } catch (Throwable) {
                // project deleted between turn and render — leave the
                // resolved view fields at their defaults.
            }
        }

        $statusId = $args['status_id'] ?? null;
        if (is_int($statusId) || (is_string($statusId) && ctype_digit($statusId))) {
            try {
                $taskStatus = $this->findStatus->execute((int) $statusId);
                $view['task_status'] = [
                    'id' => $taskStatus->id,
                    'name' => $taskStatus->name,
                    'category' => $taskStatus->category->value,
                    'color_var' => $taskStatus->category->colorVar(),
                ];
            } catch (Throwable) {
                // status deleted — leave null
            }
        }

        $result = $toolCall['result'] ?? null;
        if (is_array($result) && isset($result['task_id'])) {
            try {
                $task = $this->findTask->execute((int) $result['task_id']);
                $view['created_task_id'] = $task->id;
            } catch (Throwable) {
                $view['created_task_id'] = (int) $result['task_id'];
            }
        }

        return $view;
    }

    /**
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentCreateProject(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        $view = [
            'name' => 'create_project',
            'status' => $status,
            'project_name' => (string) ($args['name'] ?? ''),
            'description' => (string) ($args['description'] ?? ''),
            'team' => null,
            'team_id' => $this->intOrNull($args['team_id'] ?? null),
            'logo' => null,
            'default_statuses' => array_map(
                static fn (array $s): string => $s['name'],
                CreateProjectTool::defaultStatuses(),
            ),
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'created_project_id' => null,
            'available_teams' => $this->listTeamsForUser
                ->execute($user)
                ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
                ->values()
                ->all(),
        ];

        $teamId = $args['team_id'] ?? null;
        if (is_int($teamId) || (is_string($teamId) && ctype_digit($teamId))) {
            try {
                $team = $this->findTeam->execute((int) $teamId);
                $view['team'] = [
                    'id' => $team->id,
                    'name' => $team->name,
                ];
            } catch (Throwable) {
                // team deleted between turn and render — leave null
            }
        }

        $logo = $args['logo_attachment'] ?? null;
        if (is_array($logo)) {
            $url = $logo['url'] ?? null;
            if (is_string($url) && $url !== '') {
                $view['logo'] = [
                    'url' => $url,
                    'name' => is_string($logo['name'] ?? null) ? $logo['name'] : null,
                ];
            }
        }

        $result = $toolCall['result'] ?? null;
        if (is_array($result) && isset($result['project_id'])) {
            $view['created_project_id'] = (int) $result['project_id'];
        }

        return $view;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentAskUserChoice(array $toolCall): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        $rawOptions = is_array($args['options'] ?? null) ? $args['options'] : [];
        $options = array_values(array_filter(array_map(
            static fn ($o): ?string => is_string($o) ? $o : null,
            $rawOptions,
        )));

        $result = $toolCall['result'] ?? null;
        $chosenIndex = null;
        $chosenLabel = null;
        if (is_array($result)) {
            $idx = $result['choice_index'] ?? null;
            if (is_int($idx) || (is_string($idx) && ctype_digit($idx))) {
                $chosenIndex = (int) $idx;
            }
            if (is_string($result['choice_label'] ?? null)) {
                $chosenLabel = $result['choice_label'];
            }
        }

        return [
            'name' => 'ask_user_choice',
            'status' => $status,
            'question' => (string) ($args['question'] ?? ''),
            'options' => $options,
            'chosen_index' => $chosenIndex,
            'chosen_label' => $chosenLabel,
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
        ];
    }

    /**
     * Full-form preview card for create_event. Supplies the available color
     * and recurrence option catalogues so the card's swatches/select can
     * render without re-deriving from enums in the Blade.
     *
     * @param  array<string, mixed>  $toolCall
     * @return array<string, mixed>
     */
    private function presentCreateEvent(array $toolCall): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $status = (string) ($toolCall['status'] ?? 'pending');

        return [
            'name' => 'create_event',
            'status' => $status,
            'arguments' => $args,
            'available_colors' => array_map(fn (EventColor $c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'var' => $c->colorVar(),
            ], EventColor::cases()),
            'available_recurrence_freqs' => array_map(fn (RecurrenceFreq $r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ], RecurrenceFreq::cases()),
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'result' => is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [],
        ];
    }

    /**
     * Compact-write summary for update_event — builds a short German-friendly
     * line listing the changed fields. Falls back to a generic "Update" when
     * the args are absent or unrecognised.
     *
     * @param  array<string, mixed>  $args
     */
    private function updateEventSummary(array $args): string
    {
        $eventLabel = isset($args['event_id']) ? $this->eventLabel((int) $args['event_id']) : '?';

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

    private function eventLabel(int $id): string
    {
        try {
            $event = $this->findEvent->execute($id);

            return '"'.Str::limit((string) $event->title, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }
}
