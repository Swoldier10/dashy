<?php

namespace Database\Factories;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'project_status_id' => ProjectStatus::factory(),
            'created_by_user_id' => User::factory(),
            'name' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'start_date' => null,
            'end_date' => null,
            'position' => 0,
        ];
    }

    public function forProject(Project $project, ?ProjectStatus $status = null): self
    {
        return $this->state(fn () => [
            'project_id' => $project->id,
            'project_status_id' => $status?->id ?? $project->statuses()->value('id'),
        ]);
    }

    public function archived(): self
    {
        return $this->state(fn () => ['is_archived' => true]);
    }
}
