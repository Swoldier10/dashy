<?php

namespace Database\Factories;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStatusFactory extends Factory
{
    protected $model = ProjectStatus::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => fake()->word(),
            'position' => 0,
        ];
    }
}
