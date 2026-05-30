<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Services\RecordMemoryService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecordMemoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): RecordMemoryService
    {
        return app(RecordMemoryService::class);
    }

    public function test_records_a_user_scoped_memory(): void
    {
        $user = User::factory()->create();

        $pref = $this->service()->execute($user, 'user', 'Prefers dark mode');

        $this->assertStringStartsWith('memory.', $pref->key);
        $this->assertSame('Prefers dark mode', $pref->value['fact']);
        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'key' => $pref->key]);
    }

    public function test_records_a_team_scoped_memory_for_a_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $pref = $this->service()->execute($user, 'team', 'Deploys on Fridays', $team->id);

        $this->assertDatabaseHas('team_preferences', ['team_id' => $team->id, 'key' => $pref->key]);
    }

    public function test_team_scope_requires_a_team_id(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'team', 'x', null);
    }

    public function test_team_scope_rejects_a_team_the_user_is_not_in(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service()->execute($user, 'team', 'x', $team->id);
    }

    public function test_rejects_an_empty_fact(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'user', '   ');
    }

    public function test_rejects_an_over_long_fact(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'user', str_repeat('a', 2001));
    }

    public function test_rejects_an_unknown_scope(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'global', 'x');
    }
}
