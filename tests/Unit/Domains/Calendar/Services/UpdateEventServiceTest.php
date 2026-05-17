<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\UpdateEventService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_owner_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'title' => 'Old',
            'color' => EventColor::Danube->value,
        ]);

        $updated = app(UpdateEventService::class)->execute($user, $event->id, [
            'title' => 'New',
            'color' => EventColor::Shilo->value,
        ]);

        $this->assertSame('New', $updated->title);
        $this->assertSame(EventColor::Shilo, $updated->color);
    }

    public function test_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        $this->expectException(AuthorizationException::class);

        app(UpdateEventService::class)->execute($stranger, $event->id, ['title' => 'X']);
    }

    public function test_rejects_end_before_start_using_merged_state(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
        ]);

        // Only end_at is provided; the merge picks up the stored start_at.
        $this->expectException(ValidationException::class);

        app(UpdateEventService::class)->execute($user, $event->id, [
            'end_at' => '2026-06-01 08:00:00',
        ]);
    }
}
