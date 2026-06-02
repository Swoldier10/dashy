<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Auth\Services\FindUsersByIdsService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\FindLatestUserMessageImagesService;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\FindProjectStatusService;
use App\Domains\Projects\Services\FindProjectWithTeamMembersService;
use App\Domains\Projects\Services\ListProjectStatusesForProjectService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Throwable;

final class CreateTaskTool implements AiTool, PresentsToolCard
{
    public function __construct(
        private CreateTaskService $createTask,
        private FindProjectWithTeamMembersService $findProject,
        private FindLatestUserMessageImagesService $latestImages,
        private ListProjectStatusesForProjectService $listProjectStatuses,
        private FindProjectStatusService $findStatus,
        private FindUsersByIdsService $findUsers,
        private FindTaskService $findTask,
    ) {}

    public function name(): string
    {
        return 'create_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Create a task in one of the user\'s projects. Only call when the team and project are unambiguous. '
            .'`name` and `description` MUST be written in German in a professional, Scrum-style format — '
            .'imperative verb-first title, and a description with `## Beschreibung` and `## Akzeptanzkriterien` sections. '
            .'See the system rules for the exact contract.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'maxLength' => 200],
                'description' => ['type' => 'string', 'maxLength' => 5000],
                'status_id' => ['type' => 'integer'],
                'priority' => [
                    'type' => 'string',
                    'enum' => array_map(fn (TaskPriority $p) => $p->value, TaskPriority::cases()),
                ],
                'start_date' => ['type' => 'string', 'format' => 'date'],
                'end_date' => ['type' => 'string', 'format' => 'date'],
                'assignee_user_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                ],
            ],
            'required' => ['project_id', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $errors = [];

        $projectId = $arguments['project_id'] ?? null;
        $name = $arguments['name'] ?? null;

        if (! is_int($projectId) && ! ctype_digit((string) $projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }
        $projectId = (int) $projectId;

        if (! is_string($name) || trim($name) === '') {
            $errors[] = 'name is required.';
        }

        try {
            $project = $this->findProject->execute($user, $projectId);
        } catch (ModelNotFoundException|AuthorizationException) {
            return AiToolValidationResult::fail(['You do not have access to that project.']);
        }
        $project->loadMissing('statuses');

        // The LLM frequently picks a status_id from a different project. Rather
        // than failing the whole tool call (and confusing the user), silently
        // fall back to the project's default status. The user sees the chosen
        // status on the preview card before confirming.
        $statusId = isset($arguments['status_id']) ? (int) $arguments['status_id'] : null;
        if ($statusId === null || ! $project->statuses->contains('id', $statusId)) {
            $statusId = $this->defaultStatusId($project->statuses);
        }
        if ($statusId === null) {
            $errors[] = 'The project has no statuses configured.';
        }

        $priority = $arguments['priority'] ?? TaskPriority::Normal->value;
        if (! in_array($priority, array_map(fn (TaskPriority $p) => $p->value, TaskPriority::cases()), true)) {
            $errors[] = 'priority must be one of: urgent, high, normal, low.';
        }

        // Dates: the LLM occasionally hallucinates wild values (year 4096,
        // end before start, etc). Rather than failing the whole tool call,
        // silently drop implausible values — start falls back to today, end
        // falls back to start + 7 days. The user reviews on the preview card.
        $startDate = $this->parseDate($arguments['start_date'] ?? null) ?: null;
        $endDate = $this->parseDate($arguments['end_date'] ?? null) ?: null;

        if ($endDate !== null && $startDate !== null && $endDate->lt($startDate)) {
            $endDate = null;
        }

        $effectiveStart = $startDate ?? CarbonImmutable::today();
        if ($endDate === null) {
            $endDate = $effectiveStart->addDays(7);
        }

        $assigneeIds = $arguments['assignee_user_ids'] ?? [$user->id];
        if (! is_array($assigneeIds)) {
            $errors[] = 'assignee_user_ids must be an array of integers.';
            $assigneeIds = [$user->id];
        }
        $assigneeIds = array_values(array_unique(array_map('intval', $assigneeIds)));
        $teamMemberIds = $project->team->members->pluck('id')->all();
        $invalidAssignees = array_diff($assigneeIds, $teamMemberIds);
        if ($invalidAssignees !== []) {
            $errors[] = 'One or more assignees are not members of the project\'s team.';
        }

        if ($errors !== []) {
            return AiToolValidationResult::fail($errors);
        }

        $description = $arguments['description'] ?? null;
        if ($description !== null && ! is_string($description)) {
            $description = null;
        }

        return AiToolValidationResult::ok([
            'project_id' => $projectId,
            'name' => trim((string) $name),
            'description' => $description,
            'status_id' => $statusId,
            'priority' => $priority,
            'start_date' => $effectiveStart->toDateString(),
            'end_date' => $endDate->toDateString(),
            'assignee_user_ids' => $assigneeIds,
            'image_attachments' => $this->resolveImageAttachments($arguments, $chat),
        ]);
    }

    /**
     * Preserve already-snapshotted images across re-validation. Mirrors
     * CreateProjectTool::resolveLogoAttachment — on the LLM's first validation
     * `image_attachments` is absent and we snapshot from chat; on re-validation
     * after edits the key is present and we keep what's there.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<int, array{path:string,url:string,mime:?string,name:?string}>
     */
    private function resolveImageAttachments(array $arguments, ?Chat $chat): array
    {
        if (array_key_exists('image_attachments', $arguments)) {
            $existing = $arguments['image_attachments'];
            if (! is_array($existing)) {
                return [];
            }

            return collect($existing)
                ->map(function ($att): ?array {
                    if (! is_array($att)) {
                        return null;
                    }
                    $path = $att['path'] ?? null;
                    $url = $att['url'] ?? null;
                    if (! is_string($path) || ! is_string($url)) {
                        return null;
                    }

                    return [
                        'path' => $path,
                        'url' => $url,
                        'mime' => is_string($att['mime'] ?? null) ? $att['mime'] : null,
                        'name' => is_string($att['name'] ?? null) ? $att['name'] : null,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        return $this->snapshotImagesFromChat($chat);
    }

    public function execute(User $user, array $arguments): array
    {
        $project = $this->findProject->execute($user, (int) $arguments['project_id']);

        $task = $this->createTask->execute($user, $project, [
            'name' => $arguments['name'],
            'description' => $arguments['description'] ?? null,
            'project_status_id' => $arguments['status_id'],
            'priority' => $arguments['priority'],
            'start_date' => $arguments['start_date'],
            'end_date' => $arguments['end_date'] ?? null,
            'assignee_user_ids' => $arguments['assignee_user_ids'] ?? [$user->id],
            'image_attachments' => $arguments['image_attachments'] ?? [],
        ]);

        return [
            'task_id' => $task->id,
            'project_id' => $project->id,
        ];
    }

    /**
     * Pull image attachments from the most recent user message that carries any
     * — the message that prompted the LLM to emit this tool call. Snapshotted at
     * validation time so they survive intermediate text-only user messages (the
     * project-disambiguation reply, or a message between preview and confirm).
     * The DB read lives in a Chat-domain service.
     *
     * @return array<int, array<string, mixed>>
     */
    private function snapshotImagesFromChat(?Chat $chat): array
    {
        if ($chat === null) {
            return [];
        }

        return $this->latestImages->execute($chat);
    }

    /**
     * @param  Collection<int, ProjectStatus>  $statuses
     */
    private function defaultStatusId($statuses): ?int
    {
        $notStarted = $statuses->first(
            fn ($s) => $s->category === ProjectStatusCategory::NotStarted,
        );

        return ($notStarted ?? $statuses->first())?->id;
    }

    /**
     * Parse + sanity-check a date. Returns null for missing, malformed, or
     * implausibly far-out values (e.g. year 4096) so the tool gracefully
     * falls back to a default rather than failing.
     */
    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }

        $today = CarbonImmutable::today();
        if ($date->lt($today->subYears(5)) || $date->gt($today->addYears(5))) {
            return null;
        }

        return $date;
    }

    public function presentCard(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        $view = [
            'name' => $toolCall['name'] ?? null,
            'status' => (string) ($toolCall['status'] ?? 'pending'),
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
            'assignee_user_ids' => array_values(array_map('intval', (array) ($args['assignee_user_ids'] ?? []))),
            'available_priorities' => array_map(
                static fn (TaskPriority $p) => ['value' => $p->value, 'label' => $p->label(), 'color_var' => $p->colorVar()],
                TaskPriority::cases(),
            ),
            'available_statuses' => [],
            'available_assignees' => [],
        ];

        $view['images'] = array_values(array_filter(array_map(static function ($att): ?array {
            if (! is_array($att) || ! is_string($att['url'] ?? null) || $att['url'] === '') {
                return null;
            }

            return ['url' => $att['url'], 'name' => is_string($att['name'] ?? null) ? $att['name'] : null];
        }, (array) ($args['image_attachments'] ?? []))));

        $assigneeIds = array_map('intval', (array) ($args['assignee_user_ids'] ?? []));
        if ($assigneeIds !== []) {
            $view['assignees'] = $this->findUsers->execute($assigneeIds)
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                ->values()
                ->all();
        }

        $priorityValue = $args['priority'] ?? null;
        if (is_string($priorityValue) && ($priority = TaskPriority::tryFrom($priorityValue)) !== null) {
            $view['priority'] = ['value' => $priority->value, 'label' => $priority->label(), 'color_var' => $priority->colorVar()];
        }

        $projectId = $args['project_id'] ?? null;
        if (is_int($projectId) || (is_string($projectId) && ctype_digit($projectId))) {
            try {
                $project = $this->findProject->execute($user, (int) $projectId);
                $view['project'] = ['id' => $project->id, 'name' => $project->name];
                $view['available_statuses'] = $this->listProjectStatuses->execute($user, $project)
                    ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'category' => $s->category->value, 'color_var' => $s->category->colorVar()])
                    ->values()
                    ->all();
                $view['available_assignees'] = $project->team->members
                    ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])
                    ->values()
                    ->all();
            } catch (Throwable) {
                // project gone between turn and render — keep defaults.
            }
        }

        $statusId = $args['status_id'] ?? null;
        if (is_int($statusId) || (is_string($statusId) && ctype_digit($statusId))) {
            try {
                $taskStatus = $this->findStatus->execute($user, (int) $statusId);
                $view['task_status'] = ['id' => $taskStatus->id, 'name' => $taskStatus->name, 'category' => $taskStatus->category->value, 'color_var' => $taskStatus->category->colorVar()];
            } catch (Throwable) {
                // status gone — leave null
            }
        }

        $result = $toolCall['result'] ?? null;
        if (is_array($result) && isset($result['task_id'])) {
            try {
                $view['created_task_id'] = $this->findTask->execute($user, (int) $result['task_id'])->id;
            } catch (Throwable) {
                $view['created_task_id'] = (int) $result['task_id'];
            }
        }

        return $view;
    }
}
