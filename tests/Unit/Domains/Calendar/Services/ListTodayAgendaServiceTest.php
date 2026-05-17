<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Enums\AgendaKind;
use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\ListTodayAgendaService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTodayAgendaServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    private ProjectStatus $status;

    private CarbonImmutable $day;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($this->user->id, ['role' => TeamRole::Member->value]);
        $this->project = Project::factory()->create(['team_id' => $team->id]);
        $this->status = ProjectStatus::factory()->create(['project_id' => $this->project->id]);

        $this->day = CarbonImmutable::parse('2026-06-15 00:00:00');
    }

    private function makeTask(array $overrides = []): Task
    {
        return Task::factory()->create(array_merge([
            'project_id' => $this->project->id,
            'project_status_id' => $this->status->id,
        ], $overrides));
    }

    public function test_merges_events_and_tasks_sorted_by_time(): void
    {
        Event::factory()->forUser($this->user)->create([
            'title' => 'Standup',
            'start_at' => '2026-06-15 09:30:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);
        Event::factory()->forUser($this->user)->create([
            'title' => 'Client call',
            'start_at' => '2026-06-15 14:00:00',
            'end_at' => '2026-06-15 15:00:00',
        ]);
        $this->makeTask([
            'name' => 'Send plan',
            'end_date' => '2026-06-15 12:00:00',
        ]);

        $rows = app(ListTodayAgendaService::class)->executeFor($this->user, $this->day);

        $this->assertCount(3, $rows);
        $this->assertSame('Standup', $rows[0]->title);
        $this->assertSame(AgendaKind::Event, $rows[0]->kind);
        $this->assertSame('9:30', $rows[0]->timeLabel);
        $this->assertSame('Send plan', $rows[1]->title);
        $this->assertSame(AgendaKind::Task, $rows[1]->kind);
        $this->assertSame('DUE 12PM', $rows[1]->timeLabel);
        $this->assertSame('Client call', $rows[2]->title);
    }

    public function test_excludes_tasks_due_on_a_different_day(): void
    {
        $this->makeTask([
            'name' => 'Tomorrow task',
            'end_date' => '2026-06-16 09:00:00',
        ]);
        $this->makeTask([
            'name' => 'Today task',
            'end_date' => '2026-06-15 09:00:00',
        ]);

        $rows = app(ListTodayAgendaService::class)->executeFor($this->user, $this->day);

        $this->assertCount(1, $rows);
        $this->assertSame('Today task', $rows[0]->title);
    }

    public function test_excludes_tasks_without_due_date(): void
    {
        $this->makeTask([
            'name' => 'No date',
            'end_date' => null,
        ]);

        $rows = app(ListTodayAgendaService::class)->executeFor($this->user, $this->day);

        $this->assertSame([], $rows);
    }

    public function test_all_day_event_uses_all_day_label(): void
    {
        Event::factory()->forUser($this->user)->allDay()->create([
            'title' => 'Holiday',
            'start_at' => '2026-06-15 00:00:00',
            'end_at' => '2026-06-15 23:59:59',
        ]);

        $rows = app(ListTodayAgendaService::class)->executeFor($this->user, $this->day);

        $this->assertCount(1, $rows);
        $this->assertSame('ALL DAY', $rows[0]->timeLabel);
    }

    public function test_empty_day_returns_empty_list(): void
    {
        $rows = app(ListTodayAgendaService::class)->executeFor($this->user, $this->day);

        $this->assertSame([], $rows);
    }
}
