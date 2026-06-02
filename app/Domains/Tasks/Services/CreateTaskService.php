<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\AssertProjectStatusInProjectService;
use App\Domains\Tasks\Actions\AddTaskAssigneeAction;
use App\Domains\Tasks\Actions\CreateTaskAction;
use App\Domains\Tasks\Actions\NextTaskPositionAction;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Support\TaskAttachmentNormalizer;
use App\Domains\Teams\Services\ListTeamMemberIdsService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class CreateTaskService
{
    public function __construct(
        private CreateTaskAction $create,
        private NextTaskPositionAction $nextPosition,
        private AddTaskAssigneeAction $addAssignee,
        private AssertProjectStatusInProjectService $assertStatusInProject,
        private ListTeamMemberIdsService $listTeamMemberIds,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Project $project, array $input): Task
    {
        Gate::forUser($actor)->authorize('create', [Task::class, $project]);

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:200'],
            'project_status_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignee_user_ids' => ['nullable', 'array'],
            'assignee_user_ids.*' => ['integer'],
            'image_attachments' => ['nullable', 'array', 'max:10'],
            'image_attachments.*.path' => ['required_with:image_attachments.*', 'string'],
            'image_attachments.*.url' => ['required_with:image_attachments.*', 'string'],
            'image_attachments.*.mime' => ['nullable', 'string'],
            'image_attachments.*.name' => ['nullable', 'string'],
        ])->validate();

        $statusId = (int) $validated['project_status_id'];
        $this->assertStatusInProject->execute($statusId, $project->id);

        $assigneeIds = array_values(array_unique(array_map(
            'intval',
            $validated['assignee_user_ids'] ?? [],
        )));

        if ($assigneeIds !== []) {
            $memberIds = $this->listTeamMemberIds->execute($project->team);
            $invalid = array_diff($assigneeIds, $memberIds);
            if ($invalid !== []) {
                throw ValidationException::withMessages([
                    'assignee_user_ids' => __('One or more assignees are not members of this project\'s team.'),
                ]);
            }
        }

        $imageAttachments = $this->normaliseAttachments($validated['image_attachments'] ?? []);

        return DB::transaction(function () use ($actor, $project, $validated, $statusId, $assigneeIds, $imageAttachments) {
            $position = $this->nextPosition->execute($project->id, $statusId);

            $task = $this->create->execute([
                'project_id' => $project->id,
                'project_status_id' => $statusId,
                'created_by_user_id' => $actor->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'] ?? TaskPriority::Normal->value,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'position' => $position,
                'attachments' => $imageAttachments !== [] ? $imageAttachments : null,
            ]);

            foreach ($assigneeIds as $userId) {
                $this->addAssignee->execute($task, $userId, $actor->id);
            }

            return $task;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     * @return array<int, array<string, mixed>>
     */
    private function normaliseAttachments(array $attachments): array
    {
        return TaskAttachmentNormalizer::normalise($attachments);
    }
}
