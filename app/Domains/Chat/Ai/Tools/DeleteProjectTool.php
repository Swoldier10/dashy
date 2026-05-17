<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Services\DeleteProjectService;
use App\Models\User;

/**
 * CONFIRM-WRITE (destructive). Hard-deletes a project and every task, status,
 * and time entry tied to it. Only team owners can delete projects (enforced
 * in DeleteProjectService).
 */
final class DeleteProjectTool implements AiTool
{
    public function __construct(
        private DeleteProjectService $deleteProject,
    ) {}

    public function name(): string
    {
        return 'delete_project';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Hard-delete a project and every task, status, and time entry under it. Cannot be '
            .'undone. Only team owners can do this.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
            ],
            'required' => ['project_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $projectId = $arguments['project_id'] ?? null;
        if (! is_int($projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['project_id' => $projectId]);
    }

    public function execute(User $user, array $arguments): array
    {
        $this->deleteProject->execute($user, (int) $arguments['project_id']);

        return ['project_id' => (int) $arguments['project_id'], 'deleted' => true];
    }
}
