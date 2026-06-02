<?php

namespace Database\Factories;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => null,
            'actor_user_id' => null,
            'type' => NotificationType::TaskAssigned->value,
            'subject_type' => null,
            'subject_id' => null,
            'data' => [
                'task_id' => fake()->numberBetween(1, 1000),
                'task_name' => fake()->sentence(3),
                'project_name' => fake()->words(2, true),
                'actor_name' => fake()->name(),
            ],
            'dedupe_key' => null,
            'read_at' => null,
        ];
    }

    public function read(): self
    {
        return $this->state(fn () => ['read_at' => now()]);
    }

    public function ofType(NotificationType $type): self
    {
        return $this->state(fn () => ['type' => $type->value]);
    }

    public function forTeam(Team $team): self
    {
        return $this->state(fn () => ['team_id' => $team->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withData(array $data): self
    {
        return $this->state(fn () => ['data' => $data]);
    }
}
