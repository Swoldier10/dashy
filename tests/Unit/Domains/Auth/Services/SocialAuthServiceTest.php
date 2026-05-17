<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Domains\Auth\Actions\FindUserByGoogleIdAction;
use App\Domains\Auth\Actions\UpdateUserAction;
use App\Domains\Auth\Exceptions\SocialAuthException;
use App\Domains\Auth\Services\SocialAuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
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
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_returns_existing_user_when_google_id_already_linked(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser(['id' => 'g-existing']));

        $existing = User::factory()->create([
            'google_id' => 'g-existing',
            'email' => 'existing@example.com',
        ]);

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertFalse($result->isNewUser);
        $this->assertSame($existing->id, $result->user->id);
        $this->assertSame(1, User::count());
    }

    public function test_links_google_to_existing_user_when_email_matches(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'id' => 'g-link',
            'email' => 'tomerge@example.com',
            'avatar' => 'https://example.com/google-avatar.png',
        ]));

        $existing = User::factory()->create([
            'email' => 'tomerge@example.com',
            'avatar' => null,
            'google_id' => null,
            'email_verified_at' => null,
        ]);

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertFalse($result->isNewUser);
        $this->assertSame($existing->id, $result->user->id);
        $this->assertSame('g-link', $result->user->google_id);
        $this->assertSame('https://example.com/google-avatar.png', $result->user->avatar);
        $this->assertNotNull($result->user->email_verified_at);
        $this->assertSame(1, User::count());
    }

    public function test_link_preserves_existing_avatar(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'email' => 'avatar@example.com',
            'avatar' => 'https://example.com/google-avatar.png',
        ]));

        $existing = User::factory()->create([
            'email' => 'avatar@example.com',
            'avatar' => 'https://example.com/own-avatar.png',
            'google_id' => null,
        ]);

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertSame($existing->id, $result->user->id);
        $this->assertSame('https://example.com/own-avatar.png', $result->user->avatar);
    }

    public function test_link_does_not_set_verified_at_when_google_reports_unverified(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'email' => 'unverified@example.com',
            'user' => [
                'given_name' => 'No',
                'family_name' => 'Verify',
                'email_verified' => false,
            ],
        ]));

        User::factory()->create([
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
            'google_id' => null,
        ]);

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertNull($result->user->email_verified_at);
    }

    public function test_creates_new_user_when_no_match(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'id' => 'g-fresh',
            'email' => 'fresh@example.com',
            'name' => 'Fresh Face',
            'avatar' => 'https://example.com/fresh.png',
            'user' => [
                'given_name' => 'Fresh',
                'family_name' => 'Face',
                'email_verified' => true,
            ],
        ]));

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertTrue($result->isNewUser);
        $this->assertSame('fresh@example.com', $result->user->email);
        $this->assertSame('g-fresh', $result->user->google_id);
        $this->assertSame('Fresh', $result->user->first_name);
        $this->assertSame('Face', $result->user->last_name);
        $this->assertSame('Fresh Face', $result->user->name);
        $this->assertNull($result->user->password);
        $this->assertNull($result->user->salutation);
        $this->assertNotNull($result->user->email_verified_at);
    }

    public function test_creates_new_user_without_verified_at_when_google_unverified(): void
    {
        $this->bindSocialiteToReturn($this->fakeSocialiteUser([
            'email' => 'fresh-unverified@example.com',
            'user' => [
                'given_name' => 'Fresh',
                'family_name' => 'Unverified',
                'email_verified' => false,
            ],
        ]));

        $result = app(SocialAuthService::class)->handleGoogleCallback();

        $this->assertTrue($result->isNewUser);
        $this->assertNull($result->user->email_verified_at);
    }

    public function test_socialite_throwing_raises_social_auth_exception_and_does_not_create_user(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('Boom.'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->expectException(SocialAuthException::class);

        try {
            app(SocialAuthService::class)->handleGoogleCallback();
        } finally {
            $this->assertSame(0, User::count());
        }
    }
}
