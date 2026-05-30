<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Preferences\Services\ForgetMemoryService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ForgetMemoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ForgetMemoryService
    {
        return app(ForgetMemoryService::class);
    }

    public function test_forgets_a_user_scoped_memory(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'memory.x', 'value' => ['fact' => 'f']]);

        $this->assertTrue($this->service()->execute($user, 'user', 'memory.x'));
        $this->assertDatabaseMissing('user_preferences', ['user_id' => $user->id, 'key' => 'memory.x']);
    }

    public function test_returns_false_when_nothing_matched(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->service()->execute($user, 'user', 'memory.absent'));
    }

    public function test_forgets_a_team_scoped_memory_for_a_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        TeamPreference::create(['team_id' => $team->id, 'key' => 'memory.t', 'value' => ['fact' => 'f']]);

        $this->assertTrue($this->service()->execute($user, 'team', 'memory.t', $team->id));
        $this->assertDatabaseMissing('team_preferences', ['team_id' => $team->id, 'key' => 'memory.t']);
    }

    public function test_team_scope_rejects_a_non_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service()->execute($user, 'team', 'memory.t', $team->id);
    }

    public function test_rejects_an_empty_key(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'user', '  ');
    }
}
