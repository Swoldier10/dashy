<?php

namespace Tests\Feature\Profile;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('password', 'password')
            ->call('deleteAccount')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertFalse(auth()->check());
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('password', 'wrong-password')
            ->call('deleteAccount')
            ->assertHasErrors('password');

        $this->assertNotNull($user->fresh());
    }

    public function test_google_only_user_must_type_delete(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('confirmation', 'DELETE')
            ->call('deleteAccount')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
    }

    public function test_google_only_user_rejected_when_typed_wrong(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('confirmation', 'delete')
            ->call('deleteAccount')
            ->assertHasErrors('confirmation');

        $this->assertNotNull($user->fresh());
    }

    public function test_personal_team_is_cleaned_up_on_delete(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'password' => Hash::make('password'),
        ]);
        $personalTeamId = $user->teams()->where('teams.personal_team', true)->value('teams.id');
        $this->assertNotNull($personalTeamId);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('password', 'password')
            ->call('deleteAccount')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('teams', ['id' => $personalTeamId]);
    }

    public function test_solo_non_personal_teams_are_cleaned_up_on_delete(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $solo = Team::factory()->create();
        $solo->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('password', 'password')
            ->call('deleteAccount')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertDatabaseMissing('teams', ['id' => $solo->id]);
    }

    public function test_sole_owner_of_multi_member_team_blocks_deletion(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $other = User::factory()->create();
        $shared = Team::factory()->create(['name' => 'Shared Team']);
        $shared->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach($other->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($user);

        Livewire::test('settings.delete-account-modal')
            ->set('password', 'password')
            ->call('deleteAccount')
            ->assertHasErrors('team');

        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas('teams', ['id' => $shared->id]);
    }
}
