<?php

namespace Tests\Unit\Domains\Auth\DTOs;

use App\Domains\Auth\DTOs\GoogleProfile;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\TestCase;

class GoogleProfileTest extends TestCase
{
    private function socialiteUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->id = $overrides['id'] ?? '12345';
        $user->email = $overrides['email'] ?? 'pat@example.com';
        $user->name = $overrides['name'] ?? 'Pat Visitor';
        $user->avatar = $overrides['avatar'] ?? 'https://example.com/avatar.png';
        $user->user = $overrides['user'] ?? [
            'given_name' => 'Pat',
            'family_name' => 'Visitor',
            'email_verified' => true,
        ];

        return $user;
    }

    public function test_extracts_given_and_family_name_from_raw_payload(): void
    {
        $profile = GoogleProfile::fromSocialite($this->socialiteUser());

        $this->assertSame('Pat', $profile->firstName);
        $this->assertSame('Visitor', $profile->lastName);
    }

    public function test_falls_back_to_splitting_full_name_on_whitespace(): void
    {
        $profile = GoogleProfile::fromSocialite($this->socialiteUser([
            'name' => 'Alex Q Morgan',
            'user' => ['email_verified' => true],
        ]));

        $this->assertSame('Alex', $profile->firstName);
        $this->assertSame('Q Morgan', $profile->lastName);
    }

    public function test_returns_null_parts_when_full_name_is_empty(): void
    {
        $socialite = new SocialiteUser;
        $socialite->id = '1';
        $socialite->email = 'a@example.com';
        $socialite->name = null;
        $socialite->avatar = null;
        $socialite->user = ['email_verified' => false];

        $profile = GoogleProfile::fromSocialite($socialite);

        $this->assertNull($profile->firstName);
        $this->assertNull($profile->lastName);
    }

    public function test_respects_email_verified_flag(): void
    {
        $verified = GoogleProfile::fromSocialite($this->socialiteUser([
            'user' => ['email_verified' => true, 'given_name' => 'A', 'family_name' => 'B'],
        ]));
        $unverified = GoogleProfile::fromSocialite($this->socialiteUser([
            'user' => ['email_verified' => false, 'given_name' => 'A', 'family_name' => 'B'],
        ]));

        $this->assertTrue($verified->emailVerified);
        $this->assertFalse($unverified->emailVerified);
    }

    public function test_treats_missing_email_verified_as_unverified(): void
    {
        $profile = GoogleProfile::fromSocialite($this->socialiteUser([
            'user' => ['given_name' => 'A', 'family_name' => 'B'],
        ]));

        $this->assertFalse($profile->emailVerified);
    }

    public function test_carries_id_email_and_avatar(): void
    {
        $profile = GoogleProfile::fromSocialite($this->socialiteUser([
            'id' => 'abc-id',
            'email' => 'me@example.com',
            'avatar' => 'https://example.com/me.png',
        ]));

        $this->assertSame('abc-id', $profile->id);
        $this->assertSame('me@example.com', $profile->email);
        $this->assertSame('https://example.com/me.png', $profile->avatar);
    }
}
