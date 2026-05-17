<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Chat\Ai\Tools\DeleteEventTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEventToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_rejects_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::create([
            'user_id' => $owner->id,
            'title' => 'Private',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);

        $result = app(DeleteEventTool::class)->validate($stranger, [
            'event_id' => $event->id,
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_missing_event_id(): void
    {
        $user = User::factory()->create();

        $result = app(DeleteEventTool::class)->validate($user, []);

        $this->assertFalse($result->valid);
    }
}
