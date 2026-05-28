<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Actions\CreateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Actions\DeleteGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Actions\FindGoogleCalendarConnectionForUserAction;
use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoogleCalendarConnectionActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_attributes(): void
    {
        $user = User::factory()->create();

        $connection = (new CreateGoogleCalendarConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'access-1',
            'refresh_token' => 'refresh-1',
            'expires_at' => now()->addHour(),
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'account_email' => 'calendar@example.com',
            'calendar_id' => 'primary',
        ]);

        $this->assertSame('access-1', $connection->access_token);
        $this->assertSame('refresh-1', $connection->refresh_token);
        $this->assertSame('calendar@example.com', $connection->account_email);
        $this->assertDatabaseHas('google_calendar_connections', ['user_id' => $user->id]);
    }

    public function test_update_force_fills_attributes(): void
    {
        $connection = GoogleCalendarConnection::factory()->create(['access_token' => 'old']);

        $updated = (new UpdateGoogleCalendarConnectionAction)->execute($connection, [
            'access_token' => 'new',
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->assertSame('new', $updated->access_token);
    }

    public function test_delete_removes_row(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();

        (new DeleteGoogleCalendarConnectionAction)->execute($connection);

        $this->assertDatabaseMissing('google_calendar_connections', ['id' => $connection->id]);
    }

    public function test_find_returns_match_or_null(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        GoogleCalendarConnection::factory()->for($user)->create();

        $this->assertNotNull((new FindGoogleCalendarConnectionForUserAction)->execute($user));
        $this->assertNull((new FindGoogleCalendarConnectionForUserAction)->execute($other));
    }

    public function test_tokens_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        (new CreateGoogleCalendarConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'plain-access-789',
            'refresh_token' => 'plain-refresh-012',
        ]);

        $row = DB::table('google_calendar_connections')->where('user_id', $user->id)->first();
        $this->assertNotSame('plain-access-789', $row->access_token);
        $this->assertNotSame('plain-refresh-012', $row->refresh_token);
    }
}
