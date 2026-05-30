<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\ListOrphanedLinksAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListOrphanedLinksActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_links_whose_local_row_is_gone(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->create(['user_id' => $user->id]);

        $event = Event::query()->create([
            'user_id' => $user->id,
            'title' => 'kept',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'is_all_day' => false,
        ]);
        $task = Task::factory()->create();

        $linkAlive = GoogleCalendarLink::query()->create([
            'connection_id' => $connection->id,
            'syncable_type' => Event::class,
            'syncable_id' => $event->id,
            'google_event_id' => 'alive-1',
            'etag' => null,
            'last_synced_at' => Carbon::now(),
        ]);
        $linkEventOrphan = GoogleCalendarLink::query()->create([
            'connection_id' => $connection->id,
            'syncable_type' => Event::class,
            'syncable_id' => 999_999,
            'google_event_id' => 'orphan-e',
            'etag' => null,
            'last_synced_at' => Carbon::now(),
        ]);
        $linkTaskOrphan = GoogleCalendarLink::query()->create([
            'connection_id' => $connection->id,
            'syncable_type' => Task::class,
            'syncable_id' => 999_998,
            'google_event_id' => 'orphan-t',
            'etag' => null,
            'last_synced_at' => Carbon::now(),
        ]);
        $linkTaskAlive = GoogleCalendarLink::query()->create([
            'connection_id' => $connection->id,
            'syncable_type' => Task::class,
            'syncable_id' => $task->id,
            'google_event_id' => 'alive-2',
            'etag' => null,
            'last_synced_at' => Carbon::now(),
        ]);

        $orphans = (new ListOrphanedLinksAction)->execute($connection);

        $orphanIds = $orphans->pluck('id')->all();
        sort($orphanIds);
        $expected = [$linkEventOrphan->id, $linkTaskOrphan->id];
        sort($expected);
        $this->assertSame($expected, $orphanIds);
        $this->assertNotContains($linkAlive->id, $orphanIds);
        $this->assertNotContains($linkTaskAlive->id, $orphanIds);
    }

    public function test_returns_empty_when_no_orphans(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->create(['user_id' => $user->id]);

        $this->assertCount(0, (new ListOrphanedLinksAction)->execute($connection));
    }
}
