<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\UpdateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_guarded_columns_via_force_fill(): void
    {
        $user = User::factory()->unverified()->create();
        $now = now()->startOfSecond();

        $updated = (new UpdateUserAction)->execute($user, [
            'google_id' => 'google-xyz',
            'avatar' => 'https://example.com/me.png',
            'email_verified_at' => $now,
        ]);

        $this->assertSame('google-xyz', $updated->google_id);
        $this->assertSame('https://example.com/me.png', $updated->avatar);
        $this->assertNotNull($updated->email_verified_at);
        $this->assertTrue($updated->email_verified_at->equalTo($now));
    }

    public function test_persists_change_and_returns_user(): void
    {
        $user = User::factory()->create(['first_name' => 'Old']);

        $updated = (new UpdateUserAction)->execute($user, [
            'first_name' => 'New',
        ]);

        $this->assertSame('New', $updated->first_name);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'New']);
    }
}
