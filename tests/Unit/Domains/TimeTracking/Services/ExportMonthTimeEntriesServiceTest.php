<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\ExportMonthTimeEntriesService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class ExportMonthTimeEntriesServiceTest extends TestCase
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

    private function loadSheet(string $bytes): Worksheet
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        file_put_contents($tmp, $bytes);
        $spreadsheet = IOFactory::load($tmp);
        unlink($tmp);

        return $spreadsheet->getActiveSheet();
    }

    public function test_member_can_export_and_receives_dto(): void
    {
        [$user, $project, $task] = $this->bootScenario();
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $export = app(ExportMonthTimeEntriesService::class)->execute(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
            null,
        );

        $this->assertSame('zeiteintraege-folienzuschnitt-2026-05.xlsx', $export->filename);
        $this->assertNotEmpty($export->contents);
        $this->assertSame("PK\x03\x04", substr($export->contents, 0, 4));
    }

    public function test_non_member_is_denied(): void
    {
        [, $project] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ExportMonthTimeEntriesService::class)->execute(
            $stranger,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
            null,
        );
    }

    public function test_me_scope_excludes_other_users_entries(): void
    {
        [$user, $project, $task] = $this->bootScenario();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
            'notes' => 'meine arbeit',
        ]);
        TimeEntry::factory()->forTask($task)->forUser($teammate)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
            'notes' => 'kollege',
        ]);

        $export = app(ExportMonthTimeEntriesService::class)->execute(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
            $user->id,
        );

        $sheet = $this->loadSheet($export->contents);
        // One body row at 5, totals at 6, nothing else.
        $this->assertSame('meine arbeit', $sheet->getCell('D5')->getValue());
        $this->assertSame('Summe', $sheet->getCell('A6')->getValue());
        $this->assertNull($sheet->getCell('A7')->getValue());
    }

    public function test_team_scope_includes_all_members_entries(): void
    {
        [$user, $project, $task] = $this->bootScenario();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($teammate)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        $export = app(ExportMonthTimeEntriesService::class)->execute(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
            null,
        );

        $sheet = $this->loadSheet($export->contents);
        // Two body rows at 5 and 6, totals at 7.
        $this->assertNotNull($sheet->getCell('B5')->getValue());
        $this->assertNotNull($sheet->getCell('B6')->getValue());
        $this->assertSame('Summe', $sheet->getCell('A7')->getValue());
    }

    public function test_export_rejects_a_non_member_user_id(): void
    {
        [$user, $project] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(ExportMonthTimeEntriesService::class)->execute(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
            $stranger->id,
        );
    }

    public function test_filename_uses_project_slug_and_month(): void
    {
        [$user, $project] = $this->bootScenario();
        $project->update(['name' => 'Ärger & Spaß']);

        $export = app(ExportMonthTimeEntriesService::class)->execute(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-12-01'),
            null,
        );

        $this->assertSame('zeiteintraege-arger-spass-2026-12.xlsx', $export->filename);
    }
}
