<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Models\User;

/**
 * AUTO-READ. Lists every project the user can access, grouped implicitly by
 * team. Useful for disambiguating "which project" without re-asking the user.
 */
final class ListProjectsTool implements AiTool
{
    public function __construct(
        private ListProjectsForUserService $listProjects,
    ) {}

    public function name(): string
    {
        return 'list_projects';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List every project the user can access. Returns id, name, description, and team_id for '
            .'each. Already in the always-on context as a skeleton — call this only when you need the '
            .'full description text or want to refresh.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass,
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        return AiToolValidationResult::ok([]);
    }

    public function execute(User $user, array $arguments): array
    {
        $projects = $this->listProjects->execute($user);

        return [
            'count' => $projects->count(),
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => (string) $p->name,
                'description' => (string) $p->description,
                'team_id' => $p->team_id,
            ])->values()->all(),
        ];
    }
}
