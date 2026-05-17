<?php

namespace Tests\Unit\Domains\TimeTracking\Support;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Support\MonthlyTimeEntriesWorkbook;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class MonthlyTimeEntriesWorkbookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Project, 1: Task, 2: User}
     */
    private function makeContext(): array
    {
        $project = Project::factory()->create(['name' => 'Folienzuschnitt']);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create(['name' => 'Folie schneiden']);
        $user = User::factory()->create(['name' => 'Raul Neculai']);

        return [$project, $task, $user];
    }

    /**
     * Persist workbook bytes to a temp .xlsx and read them back with PhpSpreadsheet.
     */
    private function loadSheet(string $bytes): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        file_put_contents($tmp, $bytes);
        $spreadsheet = IOFactory::load($tmp);
        unlink($tmp);

        return $spreadsheet->getActiveSheet();
    }

    public function test_produces_valid_xlsx_bytes(): void
    {
        [$project] = $this->makeContext();

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Team-Einträge',
            new Collection,
        );

        $this->assertNotEmpty($bytes);
        $this->assertSame("PK\x03\x04", substr($bytes, 0, 4));
        $sheet = $this->loadSheet($bytes);
        // Sheet title uses translatedFormat — value depends on the active locale.
        $this->assertMatchesRegularExpression('/^(Mai|May) 2026$/', $sheet->getTitle());
    }

    public function test_header_row_contains_expected_columns(): void
    {
        [$project] = $this->makeContext();

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Team-Einträge',
            new Collection,
        );

        $sheet = $this->loadSheet($bytes);
        $this->assertSame('Datum', $sheet->getCell('A4')->getValue());
        $this->assertSame('Mitarbeiter', $sheet->getCell('B4')->getValue());
        $this->assertSame('Aufgabe', $sheet->getCell('C4')->getValue());
        $this->assertSame('Notizen', $sheet->getCell('D4')->getValue());
        $this->assertSame('Dauer', $sheet->getCell('E4')->getValue());
    }

    public function test_body_row_contains_entry_data(): void
    {
        [$project, $task, $user] = $this->makeContext();

        $entry = TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
            'notes' => 'Montage Audi',
        ]);

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Team-Einträge',
            TimeEntry::with(['user', 'task'])->whereKey($entry->id)->get(),
        );

        $sheet = $this->loadSheet($bytes);
        $this->assertSame('Raul Neculai', $sheet->getCell('B5')->getValue());
        $this->assertSame('Folie schneiden', $sheet->getCell('C5')->getValue());
        $this->assertSame('Montage Audi', $sheet->getCell('D5')->getValue());
        // 1 hour = 3600s = 3600/86400 = 0.0416666...
        $this->assertEqualsWithDelta(3600 / 86400, (float) $sheet->getCell('E5')->getValue(), 0.0001);
    }

    public function test_totals_row_sums_durations(): void
    {
        [$project, $task, $user] = $this->makeContext();

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-11 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-11 09:30:00'),
            'duration_seconds' => 1800,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-12 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-12 09:15:00'),
            'duration_seconds' => 900,
        ]);

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Team-Einträge',
            TimeEntry::with(['user', 'task'])->get(),
        );

        $sheet = $this->loadSheet($bytes);
        // Totals row is row 8 (header 4 + 3 body 5..7 + totals 8). Excel evaluates the SUM.
        $totalsCalculated = $sheet->getCell('E8')->getCalculatedValue();
        $this->assertEqualsWithDelta(6300 / 86400, (float) $totalsCalculated, 0.0001);
    }

    public function test_running_entry_is_flagged_and_counts_elapsed_time(): void
    {
        Carbon::setTestNow('2026-05-11 12:00:00');

        [$project, $task, $user] = $this->makeContext();
        $entry = TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-11 11:50:00'),
            'ended_at' => null,
            'duration_seconds' => null,
            'notes' => null,
        ]);

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Eigene Einträge',
            TimeEntry::with(['user', 'task'])->whereKey($entry->id)->get(),
        );

        $sheet = $this->loadSheet($bytes);
        $this->assertStringContainsString('(läuft)', (string) $sheet->getCell('D5')->getValue());
        // 10 minutes elapsed = 600s. Stored as 600/86400 in column E.
        $this->assertEqualsWithDelta(600 / 86400, (float) $sheet->getCell('E5')->getValue(), 0.0001);

        Carbon::setTestNow();
    }

    public function test_empty_collection_produces_workbook_with_zero_total(): void
    {
        [$project] = $this->makeContext();

        $bytes = MonthlyTimeEntriesWorkbook::build(
            $project,
            CarbonImmutable::parse('2026-05-01'),
            'Team-Einträge',
            new Collection,
        );

        $sheet = $this->loadSheet($bytes);
        // No body rows → totals at row 5.
        $this->assertSame('Summe', $sheet->getCell('A5')->getValue());
        $this->assertSame(0, (int) $sheet->getCell('E5')->getValue());
    }
}
