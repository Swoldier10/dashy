<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Services\UpdateProjectService;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE. Renames a project. Preserves the existing description (the
 * underlying service treats absent fields as null, so we re-pass the
 * description verbatim).
 */
final class RenameProjectTool implements AiTool
{
    public function __construct(
        private UpdateProjectService $updateProject,
        private FindProjectAction $findProject,
    ) {}

    public function name(): string
    {
        return 'rename_project';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Rename a project. Provide the project id and the new name (max 80 chars). The '
            .'description is preserved unchanged.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 80],
            ],
            'required' => ['project_id', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $projectId = $arguments['project_id'] ?? null;
        $name = $arguments['name'] ?? null;

        if (! is_int($projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }
        if (! is_string($name) || trim($name) === '') {
            return AiToolValidationResult::fail(['name is required and must be a non-empty string.']);
        }
        if (mb_strlen($name) > 80) {
            return AiToolValidationResult::fail(['name must be 80 characters or fewer.']);
        }

        return AiToolValidationResult::ok([
            'project_id' => $projectId,
            'name' => trim($name),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $existing = $this->findProject->execute((int) $arguments['project_id']);
        } catch (Throwable) {
            return ['error' => 'Project not found.'];
        }

        $updated = $this->updateProject->execute(
            $user,
            (int) $arguments['project_id'],
            [
                'name' => (string) $arguments['name'],
                'description' => $existing->description,
            ],
        );

        return ['project_id' => $updated->id, 'name' => (string) $updated->name];
    }
}
