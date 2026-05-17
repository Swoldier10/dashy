<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Services\GetProjectOverviewService;
use App\Models\User;
use Throwable;

/**
 * AUTO-READ. Returns a project plus a snapshot of its current shape: total
 * tasks, open tasks, and per-status counts. Useful for "how's the X project
 * looking?" style queries.
 */
final class GetProjectOverviewTool implements AiTool
{
    public function __construct(
        private GetProjectOverviewService $getOverview,
    ) {}

    public function name(): string
    {
        return 'get_project_overview';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Get a high-level snapshot of a project: total task count, open task count, and a per-status '
            .'breakdown. Use this before answering questions about a project\'s state.';
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
        $id = $arguments['project_id'] ?? null;
        if (! is_int($id)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['project_id' => $id]);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $overview = $this->getOverview->execute($user, (int) $arguments['project_id']);
        } catch (Throwable) {
            return ['error' => 'Project not found or not accessible.'];
        }

        $project = $overview['project'];

        return [
            'project' => [
                'id' => $project->id,
                'name' => (string) $project->name,
                'description' => (string) $project->description,
                'team_id' => $project->team_id,
            ],
            'total_tasks' => $overview['total_tasks'],
            'open_tasks' => $overview['open_tasks'],
            'by_status' => $overview['by_status'],
        ];
    }
}
