<?php

namespace Tests\Feature\Settings;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_connect_button_when_not_linked(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->assertSee('Google Calendar')
            ->assertSee('Connect Google Calendar');
    }

    public function test_shows_disconnect_and_sync_buttons_when_linked(): void
    {
        $user = User::factory()->create();
        GoogleCalendarConnection::factory()->for($user)->create([
            'account_email' => 'me@example.com',
            'last_synced_at' => now()->subMinutes(5),
        ]);
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->assertSee('me@example.com')
            ->assertSee('Sync now')
            ->assertSeeHtml('disconnect-google-calendar-button');
    }

    public function test_disconnect_button_removes_connection(): void
    {
        $user = User::factory()->create();
        GoogleCalendarConnection::factory()->for($user)->create();
        $this->actingAs($user);

        // Stub revoke endpoint to avoid hitting the network.
        Http::fake(['https://oauth2.googleapis.com/revoke' => Http::response('', 200)]);

        Livewire::test('settings.integrations-section')
            ->call('disconnectGoogleCalendar')
            ->assertHasNoErrors();

        $this->assertSame(0, GoogleCalendarConnection::count());
    }

    public function test_sync_now_runs_sync_service(): void
    {
        $user = User::factory()->create();
        GoogleCalendarConnection::factory()->for($user)->create([
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [], 'nextSyncToken' => 'tok',
            ], 200),
        ]);

        Livewire::test('settings.integrations-section')
            ->call('syncGoogleCalendarNow')
            ->assertHasNoErrors();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'googleapis.com/calendar/v3');
        });
    }

    public function test_revoked_banner_shown_when_connection_in_error(): void
    {
        $user = User::factory()->create();
        GoogleCalendarConnection::factory()->for($user)->create([
            'last_sync_error' => 'Google access revoked.',
            'last_sync_error_at' => now(),
        ]);
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->assertSee('Google access revoked.')
            ->assertSeeHtml('google-calendar-error-banner');
    }
}
