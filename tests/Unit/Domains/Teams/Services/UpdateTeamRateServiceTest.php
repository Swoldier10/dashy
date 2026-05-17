<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\UpdateTeamRateService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTeamRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_set_rate_and_currency(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $updated = app(UpdateTeamRateService::class)->execute($owner, $team, [
            'hourly_rate' => '85.00',
            'currency' => Currency::CHF->value,
        ]);

        $this->assertSame('85.00', (string) $updated->hourly_rate);
        $this->assertSame(Currency::CHF, $updated->currency);
    }

    public function test_owner_can_clear_rate_and_currency(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $team->forceFill(['hourly_rate' => '40.00', 'currency' => 'USD'])->save();

        $updated = app(UpdateTeamRateService::class)->execute($owner, $team, [
            'hourly_rate' => '',
            'currency' => '',
        ]);

        $this->assertNull($updated->hourly_rate);
        $this->assertNull($updated->currency);
    }

    public function test_member_cannot_update(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(UpdateTeamRateService::class)->execute($member, $team, [
            'hourly_rate' => '999',
            'currency' => Currency::EUR->value,
        ]);
    }

    public function test_rate_without_currency_throws_validation(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(UpdateTeamRateService::class)->execute($owner, $team, [
            'hourly_rate' => '50',
            'currency' => '',
        ]);
    }

    public function test_currency_without_rate_throws_validation(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(UpdateTeamRateService::class)->execute($owner, $team, [
            'hourly_rate' => '',
            'currency' => Currency::GBP->value,
        ]);
    }

    public function test_invalid_currency_throws_validation(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(UpdateTeamRateService::class)->execute($owner, $team, [
            'hourly_rate' => '50',
            'currency' => 'XYZ',
        ]);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
