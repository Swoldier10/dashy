<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\DeleteEventAction;
use App\Domains\Calendar\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEventActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_event(): void
    {
        $event = Event::factory()->create();

        (new DeleteEventAction)->execute($event);

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }
}
