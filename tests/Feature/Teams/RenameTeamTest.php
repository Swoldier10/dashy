<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RenameTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_rename(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Old']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newName', 'New')
            ->call('rename')
            ->assertHasNoErrors();

        $this->assertSame('New', $team->fresh()->name);
    }

    public function test_owner_can_rename_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create(['name' => 'Old Personal']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newName', 'My Place')
            ->call('rename')
            ->assertHasNoErrors();

        $fresh = $team->fresh();
        $this->assertSame('My Place', $fresh->name);
        $this->assertTrue($fresh->personal_team);
    }

    public function test_member_cannot_rename(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Stays']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newName', 'Hostile')
            ->call('rename')
            ->assertForbidden();

        $this->assertSame('Stays', $team->fresh()->name);
    }
}
