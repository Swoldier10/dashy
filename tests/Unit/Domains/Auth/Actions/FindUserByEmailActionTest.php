<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserByEmailActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_matching_user(): void
    {
        $user = User::factory()->create(['email' => 'match@example.com']);

        $found = (new FindUserByEmailAction)->execute('match@example.com');

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_returns_null_when_no_match(): void
    {
        $this->assertNull((new FindUserByEmailAction)->execute('nobody@example.com'));
    }
}
