<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\DeleteEventService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create();

        app(DeleteEventService::class)->execute($user, $event->id);

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        $this->expectException(AuthorizationException::class);

        app(DeleteEventService::class)->execute($stranger, $event->id);
    }
}
