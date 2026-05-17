<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\MoveEventService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoveEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_shifts_start_and_preserves_duration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:30:00',
        ]);

        $moved = app(MoveEventService::class)->execute($user, $event->id, '2026-06-02 14:00:00');

        $this->assertSame('2026-06-02 14:00:00', $moved->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-02 15:30:00', $moved->end_at->format('Y-m-d H:i:s'));
    }

    public function test_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        $this->expectException(AuthorizationException::class);

        app(MoveEventService::class)->execute($stranger, $event->id, '2026-06-02 14:00:00');
    }
}
