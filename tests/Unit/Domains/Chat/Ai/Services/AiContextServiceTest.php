<?php

namespace Tests\Unit\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Services\AiContextService;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiContextServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_includes_today_user_priorities_and_user_teams(): void
    {
        $user = User::factory()->create(['name' => 'Raul Neculai']);
        $team = Team::factory()->create(['name' => 'Acme']);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Web']);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'Backlog',
        ]);

        $context = app(AiContextService::class)->forUser($user);

        $this->assertSame(now()->toDateString(), $context['today']);
        $this->assertSame(['id' => $user->id, 'name' => 'Raul Neculai'], $context['user']);
        $this->assertSame(['urgent', 'high', 'normal', 'low'], $context['priorities']);

        $this->assertCount(1, $context['teams']);
        $teamCtx = $context['teams'][0];
        $this->assertSame('Acme', $teamCtx['name']);
        $this->assertCount(1, $teamCtx['projects']);
        $this->assertSame('Web', $teamCtx['projects'][0]['name']);
        $this->assertSame('Backlog', $teamCtx['projects'][0]['statuses'][0]['name']);
        $this->assertSame('not_started', $teamCtx['projects'][0]['statuses'][0]['category']);
    }

    public function test_excludes_teams_user_is_not_a_member_of(): void
    {
        $user = User::factory()->create();
        $myTeam = Team::factory()->create(['name' => 'Mine']);
        $myTeam->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $other = User::factory()->create();
        $otherTeam = Team::factory()->create(['name' => 'Theirs']);
        $otherTeam->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        $context = app(AiContextService::class)->forUser($user);

        $names = array_column($context['teams'], 'name');
        $this->assertSame(['Mine'], $names);
    }

    public function test_includes_team_members(): void
    {
        $user = User::factory()->create(['name' => 'Owner']);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $other = User::factory()->create(['name' => 'Mate']);
        $team->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        $context = app(AiContextService::class)->forUser($user);

        $names = array_column($context['teams'][0]['members'], 'name');
        sort($names);
        $this->assertSame(['Mate', 'Owner'], $names);
    }
}
