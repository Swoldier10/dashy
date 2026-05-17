<?php

namespace Database\Factories;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = CarbonImmutable::parse('next monday 10:00');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_at' => $start,
            'end_at' => $start->addHour(),
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'location' => null,
            'recurrence_freq' => RecurrenceFreq::None->value,
            'recurrence_until' => null,
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function allDay(): self
    {
        return $this->state(function (array $attrs) {
            $start = CarbonImmutable::parse($attrs['start_at'])->startOfDay();

            return [
                'is_all_day' => true,
                'start_at' => $start,
                'end_at' => $start->endOfDay(),
            ];
        });
    }

    public function recurring(RecurrenceFreq $freq, ?CarbonImmutable $until = null): self
    {
        return $this->state(fn () => [
            'recurrence_freq' => $freq->value,
            'recurrence_until' => $until?->toDateString(),
        ]);
    }
}
