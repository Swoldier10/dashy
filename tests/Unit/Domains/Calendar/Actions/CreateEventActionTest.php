<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\CreateEventAction;
use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEventActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_event_with_provided_attributes(): void
    {
        $user = User::factory()->create();

        $event = (new CreateEventAction)->execute([
            'user_id' => $user->id,
            'title' => 'Design review',
            'description' => 'Notes',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
            'is_all_day' => false,
            'color' => EventColor::Torea->value,
            'location' => 'Room 3',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
            'recurrence_until' => '2026-09-01',
        ]);

        $this->assertSame('Design review', $event->title);
        $this->assertSame('Notes', $event->description);
        $this->assertSame('Room 3', $event->location);
        $this->assertSame(EventColor::Torea, $event->color);
        $this->assertSame(RecurrenceFreq::Weekly, $event->recurrence_freq);
        $this->assertSame('2026-09-01', $event->recurrence_until->toDateString());
        $this->assertSame('2026-06-01 09:00:00', $event->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-01 10:00:00', $event->end_at->format('Y-m-d H:i:s'));
    }

    public function test_defaults_color_and_recurrence(): void
    {
        $user = User::factory()->create();

        $event = (new CreateEventAction)->execute([
            'user_id' => $user->id,
            'title' => 'Bare',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
        ]);

        $this->assertSame(EventColor::Danube, $event->color);
        $this->assertSame(RecurrenceFreq::None, $event->recurrence_freq);
        $this->assertNull($event->recurrence_until);
        $this->assertFalse($event->is_all_day);
    }
}
