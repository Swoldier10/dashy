<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSocialiteUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->id = $overrides['id'] ?? 'google-1';
        $user->email = $overrides['email'] ?? 'visitor@example.com';
        $user->name = $overrides['name'] ?? 'Pat Visitor';
        $user->avatar = $overrides['avatar'] ?? 'https://example.com/avatar.png';
        $user->user = $overrides['user'] ?? [
            'given_name' => 'Pat',
            'family_name' => 'Visitor',
            'email_verified' => true,
        ];

        return $user;
    }

    private function bindSocialiteToReturn(SocialiteUser $socialiteUser): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_route_returns_a_redirect(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_callback_creates_new_user_and_dispatches_registered(): void
    {
        Event::fake([Registered::class]);
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'id' => 'g-fresh',
            'email' => 'fresh@example.com',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::where('email', 'fresh@example.com')->firstOrFail();
        $this->assertSame('g-fresh', $user->google_id);
        $this->assertSame('Pat', $user->first_name);
        $this->assertSame('Visitor', $user->last_name);
        $this->assertNotNull($user->email_verified_at);

        $this->assertSame(1, $user->teams()->where('teams.personal_team', true)->count());

        Event::assertDispatched(Registered::class, fn (Registered $e) => $e->user->is($user));
    }

    public function test_callback_links_to_existing_user_when_emails_match(): void
    {
        Event::fake([Registered::class]);
        $existing = User::factory()->withPersonalTeam()->create([
            'email' => 'tomerge@example.com',
            'google_id' => null,
        ]);

        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'id' => 'g-link',
            'email' => 'tomerge@example.com',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $existing->refresh();
        $this->assertSame('g-link', $existing->google_id);
        $this->assertSame(1, User::count());

        // Idempotent: linking an existing user does not create a duplicate team.
        $this->assertSame(1, $existing->teams()->where('teams.personal_team', true)->count());

        Event::assertNotDispatched(Registered::class);
    }

    public function test_callback_returns_existing_user_when_google_id_already_known(): void
    {
        Event::fake([Registered::class]);
        $existing = User::factory()->create([
            'google_id' => 'g-known',
            'email' => 'known@example.com',
        ]);

        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'id' => 'g-known',
            'email' => 'known@example.com',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertSame(1, User::count());

        Event::assertNotDispatched(Registered::class);
    }

    public function test_callback_redirects_with_error_when_socialite_throws(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('Boom.'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_redirect_route_bounces_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect();
        $response->assertRedirectContains('/dashboard');
    }
}
