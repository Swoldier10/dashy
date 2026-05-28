<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Exceptions\GoogleCalendarConnectionRevokedException;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Services\EnsureFreshTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnsureFreshTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_connection_unchanged_when_token_is_fresh(): void
    {
        Http::fake();
        $connection = GoogleCalendarConnection::factory()->create([
            'access_token' => 'still-good',
            'expires_at' => now()->addHour(),
        ]);

        $result = (new EnsureFreshTokenService(new UpdateGoogleCalendarConnectionAction))
            ->execute($connection);

        $this->assertSame('still-good', $result->access_token);
        Http::assertNothingSent();
    }

    public function test_refreshes_when_token_is_expired(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'fresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $connection = GoogleCalendarConnection::factory()->create([
            'access_token' => 'stale',
            'refresh_token' => 'refresh-1',
            'expires_at' => now()->subMinute(),
        ]);

        $result = (new EnsureFreshTokenService(new UpdateGoogleCalendarConnectionAction))
            ->execute($connection);

        $this->assertSame('fresh-token', $result->access_token);
        $this->assertTrue($result->expires_at->isFuture());
    }

    public function test_marks_revoked_on_invalid_grant(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $connection = GoogleCalendarConnection::factory()->create([
            'refresh_token' => 'revoked',
            'expires_at' => now()->subMinute(),
        ]);

        $this->expectException(GoogleCalendarConnectionRevokedException::class);

        try {
            (new EnsureFreshTokenService(new UpdateGoogleCalendarConnectionAction))
                ->execute($connection);
        } finally {
            $connection->refresh();
            $this->assertNotNull($connection->last_sync_error);
            $this->assertNotNull($connection->last_sync_error_at);
        }
    }

    public function test_marks_revoked_when_refresh_token_missing(): void
    {
        Http::fake();
        $connection = GoogleCalendarConnection::factory()->create([
            'refresh_token' => null,
            'expires_at' => now()->subMinute(),
        ]);

        $this->expectException(GoogleCalendarConnectionRevokedException::class);

        (new EnsureFreshTokenService(new UpdateGoogleCalendarConnectionAction))
            ->execute($connection);
    }
}
