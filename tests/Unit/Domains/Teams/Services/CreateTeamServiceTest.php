<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Services\CreateTeamService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_team_and_attaches_creator_as_owner(): void
    {
        $user = User::factory()->create();

        $team = app(CreateTeamService::class)->execute($user, ['name' => 'Acme']);

        $this->assertSame('Acme', $team->name);
        $this->assertFalse($team->personal_team);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Owner->value,
        ]);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTeamService::class)->execute($user, ['name' => '']);
    }

    public function test_name_must_be_under_eighty_chars(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTeamService::class)->execute($user, ['name' => str_repeat('a', 81)]);
    }
}
