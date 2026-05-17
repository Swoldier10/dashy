<?php

namespace Tests\Feature\Calendar;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Models\Event;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Livewire\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_returns_user_event_in_fullcalendar_shape(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'title' => 'Standup',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 09:30:00',
            'color' => EventColor::Danube->value,
            'is_all_day' => false,
        ]);

        $payload = $this->payloadFor($user, '2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $match = collect($payload)->firstWhere('extendedProps.eventId', $event->id);
        $this->assertNotNull($match, 'Event missing from payload');
        $this->assertSame('Standup', $match['title']);
        $this->assertFalse($match['allDay']);
        $this->assertContains('fc-event-danube', $match['classNames']);
        $this->assertSame('event', $match['extendedProps']['type']);
        $this->assertStringStartsWith('2026-06-15T09:00:00', $match['start']);
        $this->assertStringStartsWith('2026-06-15T09:30:00', $match['end']);
    }

    public function test_payload_excludes_events_outside_requested_range(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'title' => 'Out of range',
            'start_at' => '2026-05-01 09:00:00',
            'end_at' => '2026-05-01 10:00:00',
        ]);

        $payload = $this->payloadFor($user, '2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $titles = array_column($payload, 'title');
        $this->assertNotContains('Out of range', $titles);
    }

    public function test_payload_excludes_other_users_events(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        Event::factory()->forUser($owner)->create([
            'title' => 'Private',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);

        $payload = $this->payloadFor($stranger, '2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $titles = array_column($payload, 'title');
        $this->assertNotContains('Private', $titles);
    }

    public function test_payload_includes_scheduled_tasks_as_non_editable(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Audit logs',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        $payload = $this->payloadFor($user, '2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $match = collect($payload)->firstWhere('extendedProps.taskId', $task->id);
        $this->assertNotNull($match);
        $this->assertSame('task', $match['extendedProps']['type']);
        $this->assertFalse($match['editable']);
        $this->assertTrue($match['allDay']);
        $this->assertContains('fc-event-task', $match['classNames']);
    }

    public function test_payload_emits_per_color_classnames(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'title' => 'Green',
            'color' => EventColor::Success->value,
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);

        $payload = $this->payloadFor($user, '2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $match = collect($payload)->firstWhere('title', 'Green');
        $this->assertNotNull($match);
        $this->assertContains('fc-event-success', $match['classNames']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function payloadFor(User $user, string $start, string $end): array
    {
        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class);

        return $component->instance()->getCalendarPayload($start, $end);
    }
}
