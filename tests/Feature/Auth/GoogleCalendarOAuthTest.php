<?php

namespace Tests\Feature\Auth;

use App\Domains\GoogleCalendar\Jobs\SyncGoogleCalendarJob;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleCalendarOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_requires_authentication(): void
    {
        $this->get(route('google-calendar.connect'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_to_google(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $driver = Mockery::mock(GoogleProvider::class);
        $driver->shouldReceive('scopes')->andReturnSelf();
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('with')->andReturnSelf();
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/oauth2/auth?fake'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $this->get(route('google-calendar.connect'))
            ->assertRedirect('https://accounts.google.com/oauth2/auth?fake');
    }

    public function test_callback_creates_connection_and_dispatches_initial_sync(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $socialiteUser = (new SocialiteUser)
            ->setRaw(['email' => 'cal@example.com'])
            ->map(['email' => 'cal@example.com', 'id' => 'gid-1', 'name' => 'Cal', 'nickname' => null, 'avatar' => null]);
        $socialiteUser->token = 'access-token-1';
        $socialiteUser->refreshToken = 'refresh-token-1';
        $socialiteUser->expiresIn = 3600;
        $socialiteUser->approvedScopes = [
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/calendar.events',
        ];

        $driver = Mockery::mock(GoogleProvider::class);
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $response = $this->get(route('google-calendar.callback'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', 'google-calendar-connected');

        $this->assertDatabaseHas('google_calendar_connections', [
            'user_id' => $user->id,
            'account_email' => 'cal@example.com',
        ]);

        Queue::assertPushed(SyncGoogleCalendarJob::class, fn ($job) => $job->userId === $user->id);
    }

    public function test_callback_rejects_when_calendar_scope_missing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $socialiteUser = (new SocialiteUser)
            ->setRaw(['email' => 'cal@example.com'])
            ->map(['email' => 'cal@example.com', 'id' => 'gid-1', 'name' => 'Cal', 'nickname' => null, 'avatar' => null]);
        $socialiteUser->token = 'tok';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = 3600;
        $socialiteUser->approvedScopes = ['openid', 'email', 'profile'];

        $driver = Mockery::mock(GoogleProvider::class);
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $response = $this->get(route('google-calendar.callback'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors('google_calendar');
        $this->assertSame(0, GoogleCalendarConnection::count());
        Queue::assertNothingPushed();
    }
}
