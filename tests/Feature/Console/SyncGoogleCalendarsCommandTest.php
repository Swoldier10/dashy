<?php

namespace Tests\Feature\Console;

use App\Domains\GoogleCalendar\Jobs\SyncGoogleCalendarJob;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncGoogleCalendarsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_a_job_per_healthy_connection(): void
    {
        Queue::fake();

        $healthyA = GoogleCalendarConnection::factory()->create();
        $healthyB = GoogleCalendarConnection::factory()->create();

        $this->artisan('google-calendar:sync-all')
            ->assertSuccessful();

        Queue::assertPushed(SyncGoogleCalendarJob::class, 2);
        Queue::assertPushed(
            SyncGoogleCalendarJob::class,
            fn ($job) => $job->userId === $healthyA->user_id
        );
        Queue::assertPushed(
            SyncGoogleCalendarJob::class,
            fn ($job) => $job->userId === $healthyB->user_id
        );
    }

    public function test_skips_revoked_connections(): void
    {
        Queue::fake();

        GoogleCalendarConnection::factory()->create();
        GoogleCalendarConnection::factory()->create([
            'last_sync_error' => 'revoked',
            'last_sync_error_at' => now(),
        ]);

        $this->artisan('google-calendar:sync-all')->assertSuccessful();

        Queue::assertPushed(SyncGoogleCalendarJob::class, 1);
    }
}
