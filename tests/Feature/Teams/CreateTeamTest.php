<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_creates_team_and_is_redirected_to_detail(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        Livewire::test('pages::teams')
            ->set('name', 'Acme')
            ->call('createTeam')
            ->assertHasNoErrors();

        $team = Team::where('name', 'Acme')->firstOrFail();
        $this->assertFalse($team->personal_team);
        $this->assertSame(
            TeamRole::Owner->value,
            $team->members()->whereKey($user->id)->first()->pivot->role->value,
        );
    }

    public function test_blank_name_is_rejected(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        Livewire::test('pages::teams')
            ->set('name', '')
            ->call('createTeam')
            ->assertHasErrors('name');
    }

    public function test_long_name_is_rejected(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        Livewire::test('pages::teams')
            ->set('name', str_repeat('a', 81))
            ->call('createTeam')
            ->assertHasErrors('name');
    }
}
