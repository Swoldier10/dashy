<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Services\CreateEventService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_event_owned_by_actor(): void
    {
        $user = User::factory()->create();

        $event = app(CreateEventService::class)->execute($user, [
            'title' => 'Standup',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
        ]);

        $this->assertSame($user->id, $event->user_id);
        $this->assertSame('Standup', $event->title);
        $this->assertSame(EventColor::Danube, $event->color);
        $this->assertSame(RecurrenceFreq::None, $event->recurrence_freq);
    }

    public function test_rejects_missing_title(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateEventService::class)->execute($user, [
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
        ]);
    }

    public function test_rejects_end_before_start(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateEventService::class)->execute($user, [
            'title' => 'Bad',
            'start_at' => '2026-06-01 10:00:00',
            'end_at' => '2026-06-01 09:00:00',
        ]);
    }

    public function test_rejects_invalid_color(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateEventService::class)->execute($user, [
            'title' => 'Bad color',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'color' => 'cocoa',
        ]);
    }

    public function test_rejects_invalid_recurrence_freq(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateEventService::class)->execute($user, [
            'title' => 'Bad recur',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'recurrence_freq' => 'fortnightly',
        ]);
    }

    public function test_accepts_recurring_attributes(): void
    {
        $user = User::factory()->create();

        $event = app(CreateEventService::class)->execute($user, [
            'title' => 'Weekly',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
            'recurrence_until' => '2026-09-01',
        ]);

        $this->assertSame(RecurrenceFreq::Weekly, $event->recurrence_freq);
        $this->assertSame('2026-09-01', $event->recurrence_until->toDateString());
    }
}
