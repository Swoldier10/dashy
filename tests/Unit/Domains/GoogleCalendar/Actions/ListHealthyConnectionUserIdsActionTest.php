<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Actions\ListHealthyConnectionUserIdsAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListHealthyConnectionUserIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_yields_user_ids_for_healthy_connections_only(): void
    {
        $healthy = User::factory()->create();
        $errored = User::factory()->create();
        GoogleCalendarConnection::factory()->create(['user_id' => $healthy->id, 'last_sync_error_at' => null]);
        GoogleCalendarConnection::factory()->create(['user_id' => $errored->id, 'last_sync_error_at' => now()]);

        $ids = iterator_to_array((new ListHealthyConnectionUserIdsAction)->execute(), false);

        $this->assertContains($healthy->id, $ids);
        $this->assertNotContains($errored->id, $ids);
    }

    public function test_yields_nothing_when_no_connections(): void
    {
        $ids = iterator_to_array((new ListHealthyConnectionUserIdsAction)->execute(), false);

        $this->assertSame([], $ids);
    }
}
