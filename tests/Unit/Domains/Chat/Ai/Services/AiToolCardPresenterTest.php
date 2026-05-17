<?php

namespace Tests\Unit\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Services\AiToolCardPresenter;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolCardPresenterTest extends TestCase
{
    use RefreshDatabase;

    private function userWithTeam(string $teamName = 'Acme'): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => $teamName]);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        return [$user, $team];
    }

    public function test_presents_create_project_pending_with_team_logo_and_default_statuses(): void
    {
        [$user, $team] = $this->userWithTeam();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_project',
            'status' => 'pending',
            'arguments' => [
                'team_id' => $team->id,
                'name' => 'Marketing-Website Relaunch',
                'description' => 'Neue Seite.',
                'logo_attachment' => [
                    'path' => 'chat-attachments/1/_pending/logo.png',
                    'url' => 'https://test/logo.png',
                    'mime' => 'image/png',
                    'name' => 'logo.png',
                ],
            ],
        ], $user);

        $this->assertSame('create_project', $view['name']);
        $this->assertSame('pending', $view['status']);
        $this->assertSame('Marketing-Website Relaunch', $view['project_name']);
        $this->assertSame('Neue Seite.', $view['description']);
        $this->assertSame(['id' => $team->id, 'name' => 'Acme'], $view['team']);
        $this->assertSame($team->id, $view['team_id']);
        $this->assertSame('https://test/logo.png', $view['logo']['url']);
        $this->assertSame('logo.png', $view['logo']['name']);
        $this->assertSame(['Zu erledigen', 'In Bearbeitung', 'Erledigt'], $view['default_statuses']);
        $this->assertNull($view['created_project_id']);
    }

    public function test_presents_create_project_lists_user_teams_for_team_picker(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create(['name' => 'Alpha']);
        $teamB = Team::factory()->create(['name' => 'Beta']);
        $teamA->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $teamB->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        // A team the user is NOT in — must NOT appear in available_teams.
        Team::factory()->create(['name' => 'Gamma']);

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_project',
            'status' => 'pending',
            'arguments' => ['team_id' => $teamA->id, 'name' => 'X'],
        ], $user);

        $names = array_map(fn ($t) => $t['name'], $view['available_teams']);
        $this->assertContains('Alpha', $names);
        $this->assertContains('Beta', $names);
        $this->assertNotContains('Gamma', $names);
    }

    public function test_presents_create_project_created_with_project_id_from_result(): void
    {
        [$user, $team] = $this->userWithTeam();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_project',
            'status' => 'created',
            'arguments' => [
                'team_id' => $team->id,
                'name' => 'Done',
            ],
            'result' => [
                'project_id' => 42,
                'team_id' => $team->id,
            ],
        ], $user);

        $this->assertSame('created', $view['status']);
        $this->assertSame(42, $view['created_project_id']);
    }

    public function test_presents_create_project_failed_surfaces_validation_errors(): void
    {
        $user = User::factory()->create();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_project',
            'status' => 'failed',
            'arguments' => ['name' => 'X'],
            'validation_errors' => ['team_id is required and must be an integer.'],
        ], $user);

        $this->assertSame('failed', $view['status']);
        $this->assertNull($view['team']);
        $this->assertSame(['team_id is required and must be an integer.'], $view['validation_errors']);
    }

    public function test_presents_create_project_with_deleted_team_leaves_team_null(): void
    {
        $user = User::factory()->create();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_project',
            'status' => 'pending',
            'arguments' => [
                'team_id' => 999999,
                'name' => 'Lost team',
            ],
        ], $user);

        $this->assertNull($view['team']);
    }

    public function test_presents_create_task_pending_includes_lookup_catalogues(): void
    {
        $user = User::factory()->create();
        $member = User::factory()->create(['name' => 'Other Member']);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $todo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'Backlog',
            'position' => 0,
        ]);
        $doing = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'Doing',
            'position' => 1,
        ]);

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'create_task',
            'status' => 'pending',
            'arguments' => [
                'project_id' => $project->id,
                'name' => 'Fix bug',
                'description' => null,
                'status_id' => $todo->id,
                'priority' => 'normal',
                'start_date' => '2026-05-10',
                'end_date' => null,
                'assignee_user_ids' => [$user->id],
                'image_attachments' => [],
            ],
        ], $user);

        $this->assertSame('create_task', $view['name']);

        // Priorities cover the enum.
        $priorityValues = array_map(fn ($p) => $p['value'], $view['available_priorities']);
        $this->assertContains('urgent', $priorityValues);
        $this->assertContains('high', $priorityValues);
        $this->assertContains('normal', $priorityValues);
        $this->assertContains('low', $priorityValues);

        // Statuses scoped to project.
        $statusIds = array_map(fn ($s) => $s['id'], $view['available_statuses']);
        $this->assertEqualsCanonicalizing([$todo->id, $doing->id], $statusIds);

        // Assignees scoped to project's team.
        $assigneeIds = array_map(fn ($a) => $a['id'], $view['available_assignees']);
        $this->assertEqualsCanonicalizing([$user->id, $member->id], $assigneeIds);

        // Currently-selected assignee ids surface for the checkbox preselect.
        $this->assertSame([$user->id], $view['assignee_user_ids']);
    }

    public function test_presents_ask_user_choice_pending_with_options(): void
    {
        $user = User::factory()->create();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'ask_user_choice',
            'status' => 'pending',
            'arguments' => [
                'question' => 'Which team should I use?',
                'options' => ['Folienzuschnitt', "Raul's Team"],
            ],
        ], $user);

        $this->assertSame('ask_user_choice', $view['name']);
        $this->assertSame('pending', $view['status']);
        $this->assertSame('Which team should I use?', $view['question']);
        $this->assertSame(['Folienzuschnitt', "Raul's Team"], $view['options']);
        $this->assertNull($view['chosen_index']);
        $this->assertNull($view['chosen_label']);
    }

    public function test_presents_ask_user_choice_answered_with_chosen_index_and_label(): void
    {
        $user = User::factory()->create();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'ask_user_choice',
            'status' => 'answered',
            'arguments' => [
                'question' => 'Which?',
                'options' => ['A', 'B'],
            ],
            'result' => [
                'choice_index' => 1,
                'choice_label' => 'B',
            ],
        ], $user);

        $this->assertSame('answered', $view['status']);
        $this->assertSame(1, $view['chosen_index']);
        $this->assertSame('B', $view['chosen_label']);
    }

    public function test_presents_ask_user_choice_failed_surfaces_validation_errors(): void
    {
        $user = User::factory()->create();

        $view = app(AiToolCardPresenter::class)->present([
            'name' => 'ask_user_choice',
            'status' => 'failed',
            'arguments' => ['question' => 'X'],
            'validation_errors' => ['options must be an array of strings.'],
        ], $user);

        $this->assertSame('failed', $view['status']);
        $this->assertSame(['options must be an array of strings.'], $view['validation_errors']);
    }
}
