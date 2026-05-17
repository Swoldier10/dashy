<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Services\RenameProjectStatusService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Renames an existing project status (kanban column). The
 * status's category is preserved.
 */
final class RenameProjectStatusTool implements AiTool
{
    public function __construct(
        private RenameProjectStatusService $renameStatus,
    ) {}

    public function name(): string
    {
        return 'rename_project_status';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Rename a project status. Provide the project_status_id and the new name (max 60 chars).';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_status_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 60],
            ],
            'required' => ['project_status_id', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $statusId = $arguments['project_status_id'] ?? null;
        $name = $arguments['name'] ?? null;

        if (! is_int($statusId)) {
            return AiToolValidationResult::fail(['project_status_id is required and must be an integer.']);
        }
        if (! is_string($name) || trim($name) === '') {
            return AiToolValidationResult::fail(['name is required and must be a non-empty string.']);
        }
        if (mb_strlen($name) > 60) {
            return AiToolValidationResult::fail(['name must be 60 characters or fewer.']);
        }

        return AiToolValidationResult::ok([
            'project_status_id' => $statusId,
            'name' => trim($name),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $status = $this->renameStatus->execute(
            $user,
            (int) $arguments['project_status_id'],
            (string) $arguments['name'],
        );

        return [
            'project_status_id' => $status->id,
            'name' => (string) $status->name,
        ];
    }
}
