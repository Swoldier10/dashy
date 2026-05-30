<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Preferences\Services\ListMemoriesService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ListMemoriesServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ListMemoriesService
    {
        return app(ListMemoriesService::class);
    }

    public function test_lists_only_memory_prefixed_user_preferences(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'memory.a', 'value' => ['fact' => 'a']]);
        UserPreference::create(['user_id' => $user->id, 'key' => 'working_hours', 'value' => ['x' => 1]]);

        $memories = $this->service()->execute($user, 'user');

        $this->assertCount(1, $memories);
        $this->assertSame('memory.a', $memories->first()->key);
    }

    public function test_lists_team_memories_for_a_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        TeamPreference::create(['team_id' => $team->id, 'key' => 'memory.t', 'value' => ['fact' => 't']]);

        $memories = $this->service()->execute($user, 'team', $team->id);

        $this->assertCount(1, $memories);
        $this->assertSame('memory.t', $memories->first()->key);
    }

    public function test_team_scope_rejects_a_non_member(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service()->execute(User::factory()->create(), 'team', Team::factory()->create()->id);
    }

    public function test_team_scope_requires_a_team_id(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), 'team', null);
    }
}
