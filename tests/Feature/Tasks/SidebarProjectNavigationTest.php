<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The teams-and-projects nav lived inside the global app sidebar before
 * the tasks-page redesign. It now lives inside the per-page Workspace
 * sidebar (see workspace-sidebar.blade.php), so these tests now assert
 * that surface instead.
 */
class SidebarProjectNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_sidebar_links_to_tasks_show(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('tasks'))
            ->assertOk()
            ->assertSee(route('tasks.show', $project), escape: false);
    }

    public function test_workspace_sidebar_marks_active_project(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk()
            ->assertSee('data-test="workspace-sidebar-project-'.$project->id.'"', escape: false);
    }

    public function test_everything_sidebar_project_links_carry_from_everything(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('tasks'))
            ->assertOk()
            ->assertSee(route('tasks.show', $project).'?from=everything', escape: false);
    }

    public function test_show_page_with_from_everything_keeps_everything_chip_active(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $response = $this->actingAs($user)
            ->get(route('tasks.show', $project).'?from=everything')
            ->assertOk();

        $this->assertChipIsActive($this->extractChipTag($response->getContent(), 'everything'));
        $this->assertChipIsInactive($this->extractChipTag($response->getContent(), (string) $team->id));
    }

    public function test_show_page_without_from_param_keeps_team_chip_active(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $response = $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk();

        $this->assertChipIsActive($this->extractChipTag($response->getContent(), (string) $team->id));
        $this->assertChipIsInactive($this->extractChipTag($response->getContent(), 'everything'));
    }

    public function test_show_page_with_from_everything_keeps_sidebar_in_grouped_layout(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Demo Team']);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('tasks.show', $project).'?from=everything')
            ->assertOk()
            // Grouped layout shows the "All tasks (Everything)" entry, not "All in team".
            ->assertSee('All tasks (Everything)')
            ->assertDontSee('All in team');
    }

    /**
     * Returns the opening <a …> tag for the team chip with the given suffix
     * (e.g. "everything" or a team id), so tests can assert on its attributes.
     */
    private function extractChipTag(string $html, string $suffix): string
    {
        preg_match(
            '/<a[^>]*data-test="tasks-team-chip-'.preg_quote($suffix, '/').'"[^>]*>/',
            $html,
            $match
        );

        return $match[0] ?? '';
    }

    /**
     * Active chip uses `background-color: var(--surface)` + a border-mid ring.
     * Inactive uses `transparent` + `box-shadow: none`.
     */
    private function assertChipIsActive(string $chipTag): void
    {
        $this->assertNotSame('', $chipTag, 'Chip tag not found in response.');
        $this->assertStringContainsString('var(--border-mid)', $chipTag);
        $this->assertStringNotContainsString('box-shadow: none', $chipTag);
    }

    private function assertChipIsInactive(string $chipTag): void
    {
        $this->assertNotSame('', $chipTag, 'Chip tag not found in response.');
        $this->assertStringContainsString('box-shadow: none', $chipTag);
        $this->assertStringNotContainsString('var(--border-mid)', $chipTag);
    }
}
