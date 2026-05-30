<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\FindUserByEmailService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserByEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): FindUserByEmailService
    {
        return app(FindUserByEmailService::class);
    }

    public function test_returns_null_for_a_blank_email(): void
    {
        $this->assertNull($this->service()->execute(User::factory()->create(), '   '));
    }

    public function test_returns_null_for_an_unknown_email(): void
    {
        $this->assertNull($this->service()->execute(User::factory()->create(), 'nobody@example.com'));
    }

    public function test_returns_the_actor_when_looking_up_self(): void
    {
        $actor = User::factory()->create(['email' => 'me@example.com']);

        $found = $this->service()->execute($actor, 'me@example.com');

        $this->assertNotNull($found);
        $this->assertTrue($found->is($actor));
    }

    public function test_returns_a_user_who_shares_a_team(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['email' => 'mate@example.com']);
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $target->id => ['role' => TeamRole::Member->value],
        ]);

        $this->assertTrue($this->service()->execute($actor, 'mate@example.com')->is($target));
    }

    public function test_hides_a_user_who_shares_no_team_with_the_actor(): void
    {
        $actor = User::factory()->create();
        User::factory()->create(['email' => 'stranger@example.com']);

        $this->assertNull($this->service()->execute($actor, 'stranger@example.com'));
    }
}
