<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Enums\Salutation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_with_passthrough_attribute_array(): void
    {
        $user = (new CreateUserAction)->execute([
            'salutation' => 'mr',
            'first_name' => 'Lina',
            'last_name' => 'Marsh',
            'name' => 'Mr Lina Marsh',
            'email' => 'lina@example.com',
            'password' => 'plain-password',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Lina',
            'last_name' => 'Marsh',
            'name' => 'Mr Lina Marsh',
            'email' => 'lina@example.com',
        ]);
        $this->assertSame(Salutation::Mr, $user->salutation);
        $this->assertNotEmpty($user->password);
        $this->assertNotSame('plain-password', $user->password, 'Password should be hashed via cast.');
    }

    public function test_writes_guarded_email_verified_at_via_force_fill(): void
    {
        $now = now()->startOfSecond();

        $user = (new CreateUserAction)->execute([
            'first_name' => 'Verified',
            'last_name' => 'User',
            'name' => 'Verified User',
            'email' => 'verified@example.com',
            'password' => null,
            'email_verified_at' => $now,
        ]);

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->email_verified_at->equalTo($now));
    }

    public function test_accepts_nulls_for_oauth_only_users(): void
    {
        $user = (new CreateUserAction)->execute([
            'salutation' => null,
            'first_name' => 'OAuth',
            'last_name' => 'Only',
            'name' => 'OAuth Only',
            'email' => 'oauth@example.com',
            'password' => null,
            'google_id' => 'google-123',
            'avatar' => 'https://example.com/avatar.png',
            'email_verified_at' => now(),
        ]);

        $this->assertNull($user->password);
        $this->assertNull($user->salutation);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('https://example.com/avatar.png', $user->avatar);
    }
}
