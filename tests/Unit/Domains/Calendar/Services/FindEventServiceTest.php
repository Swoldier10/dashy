<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\FindEventService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create();

        $found = app(FindEventService::class)->execute($user, $event->id);

        $this->assertSame($event->id, $found->id);
    }

    public function test_blocks_non_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        $this->expectException(AuthorizationException::class);

        app(FindEventService::class)->execute($stranger, $event->id);
    }
}
