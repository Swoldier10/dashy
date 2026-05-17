<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $this->get(route('teams.index'))->assertRedirect(route('login'));
    }

    public function test_lists_users_teams_with_role_and_personal_badge(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $shared = Team::factory()->create(['name' => 'Acme']);
        $shared->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $response = $this->actingAs($user)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSeeText('Personal');
        $response->assertSeeText('Acme');
        $response->assertSeeText('Owner');
        $response->assertSeeText('Member');
    }

    public function test_does_not_list_other_users_teams(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $stranger = User::factory()->create();
        $strangerTeam = Team::factory()->create(['name' => 'StrangerCo']);
        $strangerTeam->members()->attach($stranger->id, ['role' => TeamRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('teams.index'));

        $response->assertOk();
        $response->assertDontSeeText('StrangerCo');
    }
}
