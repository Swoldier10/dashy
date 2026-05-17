<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\UpdateEventAction;
use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateEventActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_fillable_attributes(): void
    {
        $event = Event::factory()->create([
            'title' => 'Old',
            'color' => EventColor::Danube->value,
        ]);

        $updated = (new UpdateEventAction)->execute($event, [
            'title' => 'New',
            'color' => EventColor::Shilo->value,
        ]);

        $this->assertSame('New', $updated->title);
        $this->assertSame(EventColor::Shilo, $updated->color);
    }
}
