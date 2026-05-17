<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\ResizeEventService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResizeEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_extends_end_at(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
        ]);

        $resized = app(ResizeEventService::class)->execute($user, $event->id, '2026-06-01 11:00:00');

        $this->assertSame('2026-06-01 09:00:00', $resized->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-01 11:00:00', $resized->end_at->format('Y-m-d H:i:s'));
    }

    public function test_rejects_sub_fifteen_minute_duration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
        ]);

        $this->expectException(ValidationException::class);

        app(ResizeEventService::class)->execute($user, $event->id, '2026-06-01 09:10:00');
    }

    public function test_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        $this->expectException(AuthorizationException::class);

        app(ResizeEventService::class)->execute($stranger, $event->id, '2026-06-01 11:00:00');
    }
}
