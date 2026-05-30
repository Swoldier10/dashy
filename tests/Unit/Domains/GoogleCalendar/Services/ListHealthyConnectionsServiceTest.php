<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Services\ListHealthyConnectionsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListHealthyConnectionsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_yields_healthy_only(): void
    {
        $healthy = User::factory()->create();
        $errored = User::factory()->create();
        GoogleCalendarConnection::factory()->create(['user_id' => $healthy->id, 'last_sync_error_at' => null]);
        GoogleCalendarConnection::factory()->create(['user_id' => $errored->id, 'last_sync_error_at' => now()]);

        $ids = iterator_to_array(app(ListHealthyConnectionsService::class)->execute(), false);

        $this->assertContains($healthy->id, $ids);
        $this->assertNotContains($errored->id, $ids);
    }
}
