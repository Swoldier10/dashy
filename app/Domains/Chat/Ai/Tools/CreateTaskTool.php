<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

final class CreateTaskTool implements AiTool
{
    public function __construct(
        private CreateTaskService $createTask,
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

        $project = Project::query()
            ->with(['team.members:id', 'statuses'])
            ->find($projectId);

        if ($project === null || ! $project->team->members->contains('id', $user->id)) {
            return AiToolValidationResult::fail(['You do not have access to that project.']);
        }

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
        $project = Project::query()->findOrFail((int) $arguments['project_id']);

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
     * Pull image attachments from the most recent user message in the chat —
     * the message that prompted the LLM to emit this tool call. Attachments
     * are snapshotted at validation time so they survive intermediate user
     * messages between preview and confirm.
     *
     * @return array<int, array<string, mixed>>
     */
    private function snapshotImagesFromChat(?Chat $chat): array
    {
        if ($chat === null) {
            return [];
        }

        $latest = $chat->messages()
            ->where('role', MessageRole::User->value)
            ->orderByDesc('id')
            ->first(['attachments']);

        if (! $latest instanceof Message) {
            return [];
        }

        return collect($latest->attachments ?? [])
            ->where('type', 'image')
            ->map(fn (array $att) => [
                'path' => $att['path'] ?? null,
                'url' => $att['url'] ?? null,
                'mime' => $att['mime'] ?? null,
                'name' => $att['name'] ?? null,
            ])
            ->filter(fn (array $att) => is_string($att['path']) && is_string($att['url']))
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Domains\Projects\Models\ProjectStatus>  $statuses
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
}
