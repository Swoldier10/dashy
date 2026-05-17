<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Chat\Ai\Tools\UpdateEventTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateEventToolTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnedEvent(User $user): Event
    {
        return Event::create([
            'user_id' => $user->id,
            'title' => 'Initial',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);
    }

    public function test_validate_rejects_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($stranger, [
            'event_id' => $event->id,
            'title' => 'Hacked',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_when_nothing_to_update(): void
    {
        $owner = User::factory()->create();
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($owner, [
            'event_id' => $event->id,
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_end_before_start_when_both_provided(): void
    {
        $owner = User::factory()->create();
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($owner, [
            'event_id' => $event->id,
            'start_at' => '2026-06-15T11:00',
            'end_at' => '2026-06-15T10:00',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_uses_persisted_end_when_only_start_provided(): void
    {
        $owner = User::factory()->create();
        // Persisted end is 10:00; new start 09:30 is fine.
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($owner, [
            'event_id' => $event->id,
            'start_at' => '2026-06-15T09:30',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame('2026-06-15T09:30', $result->normalized['start_at']);
    }

    public function test_validate_drops_recurrence_until_when_freq_set_to_none(): void
    {
        $owner = User::factory()->create();
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($owner, [
            'event_id' => $event->id,
            'recurrence_freq' => RecurrenceFreq::None->value,
            'recurrence_until' => '2026-12-31',
        ]);

        $this->assertTrue($result->valid);
        $this->assertNull($result->normalized['recurrence_until']);
    }

    public function test_validate_rejects_invalid_color(): void
    {
        $owner = User::factory()->create();
        $event = $this->makeOwnedEvent($owner);

        $result = app(UpdateEventTool::class)->validate($owner, [
            'event_id' => $event->id,
            'color' => 'puce',
        ]);

        $this->assertFalse($result->valid);
    }
}
