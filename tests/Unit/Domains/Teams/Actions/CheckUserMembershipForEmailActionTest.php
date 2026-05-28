<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\CheckUserMembershipForEmailAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckUserMembershipForEmailActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_user_is_a_member(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'm@example.com']);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new CheckUserMembershipForEmailAction)->execute($team, 'm@example.com'));
    }

    public function test_returns_false_when_user_exists_but_not_on_team(): void
    {
        $team = Team::factory()->create();
        User::factory()->create(['email' => 'other@example.com']);

        $this->assertFalse((new CheckUserMembershipForEmailAction)->execute($team, 'other@example.com'));
    }

    public function test_returns_false_when_email_has_no_account(): void
    {
        $team = Team::factory()->create();

        $this->assertFalse((new CheckUserMembershipForEmailAction)->execute($team, 'nope@example.com'));
    }
}
