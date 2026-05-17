<?php

namespace Database\Factories;

use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'logo' => null,
        ];
    }
}
