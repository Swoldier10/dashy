<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Services\DeleteProjectStatusService;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE. Deletes an empty project status. The service throws if any
 * task still references the status — the validation error then surfaces on
 * the card and the user can move/delete those tasks first.
 */
final class DeleteProjectStatusTool implements AiTool
{
    public function __construct(
        private DeleteProjectStatusService $deleteStatus,
    ) {}

    public function name(): string
    {
        return 'delete_project_status';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Delete a project status column. The column must be empty — if tasks still reference it, '
            .'the call fails and the user must move/delete those tasks first.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_status_id' => ['type' => 'integer'],
            ],
            'required' => ['project_status_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $statusId = $arguments['project_status_id'] ?? null;
        if (! is_int($statusId)) {
            return AiToolValidationResult::fail(['project_status_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['project_status_id' => $statusId]);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $this->deleteStatus->execute($user, (int) $arguments['project_status_id']);
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        return ['project_status_id' => (int) $arguments['project_status_id'], 'deleted' => true];
    }
}
