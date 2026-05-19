<?php

namespace Tests\Feature\Calendar;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Livewire\Calendar;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_renders_for_authed_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('calendar'))
            ->assertOk()
            ->assertSeeLivewire(Calendar::class);
    }

    public function test_unauth_redirects_to_login(): void
    {
        $this->get(route('calendar'))->assertRedirect(route('login'));
    }

    public function test_url_params_drive_view_and_anchor(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'day', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->assertSet('view', 'day')
            ->assertSet('anchor', '2026-06-15');
    }

    public function test_prev_next_today_shift_anchor(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->call('next')
            ->assertSet('anchor', '2026-06-22')
            ->call('prev')
            ->call('prev')
            ->assertSet('anchor', '2026-06-08')
            ->call('goToday')
            ->assertSet('anchor', CarbonImmutable::now()->toDateString());
    }

    public function test_smart_title_week_view_shows_this_week_for_today_anchor(): void
    {
        CarbonImmutable::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => CarbonImmutable::now()->toDateString()])
            ->test(Calendar::class);

        $this->assertSame(__('This week'), $component->instance()->displayTitle);
    }

    public function test_smart_title_week_view_falls_back_to_range_when_far_from_today(): void
    {
        CarbonImmutable::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class);

        $title = $component->instance()->displayTitle;

        $this->assertStringContainsString('–', $title);
        $this->assertStringContainsString('Jun', $title);
    }

    public function test_smart_title_day_view_shows_today_for_today_anchor(): void
    {
        CarbonImmutable::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'day', 'date' => CarbonImmutable::now()->toDateString()])
            ->test(Calendar::class);

        $this->assertSame(__('Today'), $component->instance()->displayTitle);
    }

    public function test_create_event_opens_drawer_prefilled(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->call('createEvent', '2026-06-15 10:00:00', '2026-06-15 11:00:00')
            ->assertSet('drawerCreateMode', true)
            ->assertSet('formStartAt', '2026-06-15T10:00')
            ->assertSet('formEndAt', '2026-06-15T11:00');
    }

    public function test_submit_create_persists_event(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->set('drawerCreateMode', true)
            ->set('formTitle', 'Demo')
            ->set('formStartAt', '2026-06-15T10:00')
            ->set('formEndAt', '2026-06-15T11:00')
            ->call('submitCreate');

        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $user->id,
            'title' => 'Demo',
        ]);
    }

    public function test_submit_create_persists_exact_datetime_from_picker(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->set('drawerCreateMode', true)
            ->set('formTitle', 'Off-hour sync')
            ->set('formStartAt', '2026-06-15T09:35')
            ->set('formEndAt', '2026-06-15T10:05')
            ->call('submitCreate')
            ->assertHasNoErrors();

        $event = Event::query()->where('user_id', $user->id)->where('title', 'Off-hour sync')->first();
        $this->assertNotNull($event);
        $this->assertSame('2026-06-15 09:35:00', $event->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-15 10:05:00', $event->end_at->format('Y-m-d H:i:s'));
    }

    public function test_move_event_preserves_duration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:30:00',
        ]);

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->call('moveEvent', $event->id, '2026-06-16 14:00:00');

        $event->refresh();
        $this->assertSame('2026-06-16 14:00:00', $event->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-16 15:30:00', $event->end_at->format('Y-m-d H:i:s'));
    }

    public function test_resize_event_rejects_sub_fifteen_minute_duration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->call('resizeEvent', $event->id, '2026-06-15 09:10:00')
            ->assertHasErrors('end_at');

        $event->refresh();
        $this->assertSame('2026-06-15 10:00:00', $event->end_at->format('Y-m-d H:i:s'));
    }

    public function test_open_and_delete_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => $event->start_at->toDateString()])
            ->test(Calendar::class)
            ->call('openEventDetail', $event->id)
            ->assertSet('detailEventId', $event->id)
            ->call('deleteEvent');

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_blocks_actions_on_other_users_events(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->forUser($owner)->create();

        Livewire::actingAs($stranger)
            ->withQueryParams(['view' => 'week', 'date' => $event->start_at->toDateString()])
            ->test(Calendar::class)
            ->call('moveEvent', $event->id, '2026-06-16 14:00:00')
            ->assertForbidden();
    }

    public function test_task_overlay_shows_member_project_tasks(): void
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

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class);

        $payload = $component->instance()->getCalendarPayload('2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $titles = array_column($payload, 'title');
        $this->assertContains($task->name, $titles);
    }

    public function test_task_overlay_hides_tasks_from_non_member_projects(): void
    {
        $stranger = User::factory()->create();
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Audit logs',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        $component = Livewire::actingAs($stranger)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class);

        $payload = $component->instance()->getCalendarPayload('2026-06-15T00:00:00', '2026-06-21T23:59:59');

        $titles = array_column($payload, 'title');
        $this->assertNotContains($task->name, $titles);
    }

    public function test_recurring_event_appears_in_range_after_anchor(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'title' => 'Weekly standup',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
        ]);

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-08-03'])
            ->test(Calendar::class);

        $payload = $component->instance()->getCalendarPayload('2026-08-03T00:00:00', '2026-08-09T23:59:59');

        $titles = array_column($payload, 'title');
        $this->assertContains('Weekly standup', $titles);
    }

    public function test_set_anchor_updates_anchor(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-05-11'])
            ->test(Calendar::class)
            ->call('setAnchor', '2026-05-22')
            ->assertSet('anchor', '2026-05-22');
    }

    public function test_set_anchor_ignores_invalid_input(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-05-11'])
            ->test(Calendar::class)
            ->call('setAnchor', 'not-a-date')
            ->assertSet('anchor', '2026-05-11');
    }

    public function test_mini_month_weeks_returns_six_rows_seven_cols_monday_first(): void
    {
        CarbonImmutable::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-05-16'])
            ->test(Calendar::class);

        $weeks = $component->instance()->miniMonthWeeks;

        $this->assertCount(6, $weeks);
        foreach ($weeks as $row) {
            $this->assertCount(7, $row);
        }

        // May 2026: May 1 is a Friday. The Monday-first grid covering this month
        // starts on Monday Apr 27, 2026.
        $this->assertSame('2026-04-27', $weeks[0][0]['date']->toDateString());
        $this->assertSame(1, $weeks[0][0]['date']->dayOfWeek); // CarbonImmutable::MONDAY === 1

        // Today (May 16) lives at row 2 (Mon May 11 starts row index 2), Sat column (index 5).
        $todayCell = $weeks[2][5];
        $this->assertSame('2026-05-16', $todayCell['date']->toDateString());
        $this->assertTrue($todayCell['isToday']);
        $this->assertTrue($todayCell['inMonth']);

        // First row's first cell is outside the anchor month (April).
        $this->assertFalse($weeks[0][0]['inMonth']);
    }
}
