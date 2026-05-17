<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Livewire\Projects\ProjectDashboardPanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProjectDashboardExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Project, 2: Task}
     */
    private function bootScenario(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Folienzuschnitt']);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        return [$user, $project, $task];
    }

    public function test_member_downloads_excel_with_expected_filename(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->call('exportMonth')
            ->assertFileDownloaded(
                'zeiteintraege-folienzuschnitt-2026-05.xlsx',
                null,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            );

        Carbon::setTestNow();
    }

    public function test_me_scope_only_exports_actor_entries(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
            'notes' => 'mine',
        ]);
        TimeEntry::factory()->forTask($task)->forUser($teammate)->create([
            'started_at' => Carbon::parse('2026-05-10 11:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
            'notes' => 'teammate',
        ]);

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->call('exportMonth');

        $effect = $component->effects['download'] ?? null;
        $this->assertNotNull($effect);
        $bytes = base64_decode($effect['content']);

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        file_put_contents($tmp, $bytes);
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        unlink($tmp);

        // Row 5 is the only body row, row 6 should be the totals row.
        $this->assertSame('mine', $sheet->getCell('D5')->getValue());
        $this->assertSame('Summe', $sheet->getCell('A6')->getValue());

        Carbon::setTestNow();
    }

    public function test_team_scope_exports_all_member_entries(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($teammate)->create([
            'started_at' => Carbon::parse('2026-05-10 11:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->call('setScope', 'team')
            ->call('exportMonth');

        $effect = $component->effects['download'] ?? null;
        $bytes = base64_decode($effect['content']);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        file_put_contents($tmp, $bytes);
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        unlink($tmp);

        $this->assertNotNull($sheet->getCell('B5')->getValue());
        $this->assertNotNull($sheet->getCell('B6')->getValue());
        $this->assertSame('Summe', $sheet->getCell('A7')->getValue());

        Carbon::setTestNow();
    }
}
