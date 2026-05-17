<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\FindEventAction;
use App\Domains\Calendar\Models\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindEventActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_event(): void
    {
        $event = Event::factory()->create();

        $found = (new FindEventAction)->execute($event->id);

        $this->assertSame($event->id, $found->id);
    }

    public function test_throws_for_unknown_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindEventAction)->execute(999_999);
    }
}
