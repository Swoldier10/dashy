<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateRateTest extends TestCase
{
    use RefreshDatabase;

    private function teamWithOwner(User $owner): Team
    {
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return $team;
    }

    public function test_owner_can_set_rate_and_currency(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '125.50')
            ->set('currency', Currency::CHF->value)
            ->call('updateRate')
            ->assertHasNoErrors();

        $fresh = $team->fresh();
        $this->assertSame('125.50', (string) $fresh->hourly_rate);
        $this->assertSame(Currency::CHF, $fresh->currency);
    }

    public function test_owner_can_clear_rate_and_currency(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $team->forceFill(['hourly_rate' => '80.00', 'currency' => 'EUR'])->save();
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '')
            ->set('currency', '')
            ->call('updateRate')
            ->assertHasNoErrors();

        $fresh = $team->fresh();
        $this->assertNull($fresh->hourly_rate);
        $this->assertNull($fresh->currency);
    }

    public function test_rate_without_currency_fails(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '100')
            ->set('currency', '')
            ->call('updateRate')
            ->assertHasErrors(['currency']);

        $this->assertNull($team->fresh()->hourly_rate);
    }

    public function test_currency_without_rate_fails(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '')
            ->set('currency', Currency::USD->value)
            ->call('updateRate')
            ->assertHasErrors(['hourly_rate']);

        $this->assertNull($team->fresh()->currency);
    }

    public function test_negative_rate_fails(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '-5')
            ->set('currency', Currency::EUR->value)
            ->call('updateRate')
            ->assertHasErrors(['hourly_rate']);

        $this->assertNull($team->fresh()->hourly_rate);
    }

    public function test_non_numeric_rate_fails(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', 'abc')
            ->set('currency', Currency::EUR->value)
            ->call('updateRate')
            ->assertHasErrors(['hourly_rate']);

        $this->assertNull($team->fresh()->hourly_rate);
    }

    public function test_invalid_currency_code_fails(): void
    {
        $owner = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '50')
            ->set('currency', 'XYZ')
            ->call('updateRate')
            ->assertHasErrors(['currency']);

        $this->assertNull($team->fresh()->currency);
    }

    public function test_member_cannot_update_rate(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->teamWithOwner($owner);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('hourlyRate', '999')
            ->set('currency', Currency::USD->value)
            ->call('updateRate')
            ->assertForbidden();

        $fresh = $team->fresh();
        $this->assertNull($fresh->hourly_rate);
        $this->assertNull($fresh->currency);
    }
}
