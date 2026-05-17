<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\FindUserByGoogleIdAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserByGoogleIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_matching_user(): void
    {
        $user = User::factory()->create(['google_id' => 'google-abc']);

        $found = (new FindUserByGoogleIdAction)->execute('google-abc');

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_returns_null_when_no_match(): void
    {
        $this->assertNull((new FindUserByGoogleIdAction)->execute('nope'));
    }
}
