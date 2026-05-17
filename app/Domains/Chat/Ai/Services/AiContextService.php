<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Actions\LoadAiWorkspaceContextAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Teams\Models\Team;
use App\Models\User;

final class AiContextService
{
    public function __construct(
        private LoadAiWorkspaceContextAction $loadWorkspace,
    ) {}

    /**
     * Compact JSON-ready array for the LLM. Numeric IDs everywhere so the model
     * emits IDs (no fuzzy server-side name matching). Members live at the team
     * level because in this codebase project access == team membership.
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $teams = $this->loadWorkspace->execute($user);

        return [
            'today' => now()->toDateString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'priorities' => array_map(
                fn (TaskPriority $p) => $p->value,
                TaskPriority::cases(),
            ),
            'teams' => $teams->map(fn (Team $team) => [
                'id' => $team->id,
                'name' => $team->name,
                'members' => $team->members
                    ->map(fn (User $m) => ['id' => $m->id, 'name' => $m->name])
                    ->values()
                    ->all(),
                'projects' => $team->projects
                    ->map(fn (Project $project) => [
                        'id' => $project->id,
                        'name' => $project->name,
                        'statuses' => $project->statuses
                            ->map(fn (ProjectStatus $s) => [
                                'id' => $s->id,
                                'name' => $s->name,
                                'category' => $s->category->value,
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all(),
            ])->values()->all(),
        ];
    }
}
