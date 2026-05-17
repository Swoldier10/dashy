<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Services\CreateProjectStatusService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Creates a new status (kanban column) on a project.
 * Categories: not_started | active | done — drives sorting and colour.
 */
final class AddProjectStatusTool implements AiTool
{
    public function __construct(
        private CreateProjectStatusService $createStatus,
    ) {}

    public function name(): string
    {
        return 'add_project_status';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Add a new status column to a project. Provide the project_id, a category '
            .'(not_started | active | done), and a name (max 60 chars).';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'category' => [
                    'type' => 'string',
                    'enum' => array_map(fn (ProjectStatusCategory $c) => $c->value, ProjectStatusCategory::cases()),
                ],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 60],
            ],
            'required' => ['project_id', 'category', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $projectId = $arguments['project_id'] ?? null;
        $category = $arguments['category'] ?? null;
        $name = $arguments['name'] ?? null;

        if (! is_int($projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }
        if (! is_string($category) || ProjectStatusCategory::tryFrom($category) === null) {
            return AiToolValidationResult::fail(['category must be one of: not_started, active, done.']);
        }
        if (! is_string($name) || trim($name) === '') {
            return AiToolValidationResult::fail(['name is required and must be a non-empty string.']);
        }
        if (mb_strlen($name) > 60) {
            return AiToolValidationResult::fail(['name must be 60 characters or fewer.']);
        }

        return AiToolValidationResult::ok([
            'project_id' => $projectId,
            'category' => $category,
            'name' => trim($name),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $status = $this->createStatus->execute(
            $user,
            (int) $arguments['project_id'],
            ProjectStatusCategory::from((string) $arguments['category']),
            (string) $arguments['name'],
        );

        return [
            'project_status_id' => $status->id,
            'name' => (string) $status->name,
            'category' => $status->category->value,
        ];
    }
}
