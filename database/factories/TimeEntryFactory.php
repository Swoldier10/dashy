<?php

namespace Database\Factories;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $duration = fake()->numberBetween(60, 3 * 60 * 60);
        $startedAt = Carbon::now()->subSeconds($duration + fake()->numberBetween(0, 86400));

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addSeconds($duration),
            'duration_seconds' => $duration,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function running(): self
    {
        return $this->state(fn () => [
            'started_at' => Carbon::now()->subMinutes(fake()->numberBetween(1, 30)),
            'ended_at' => null,
            'duration_seconds' => null,
        ]);
    }

    public function forTask(Task $task): self
    {
        return $this->state(fn () => ['task_id' => $task->id]);
    }

    public function forUser(User $user): self
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
