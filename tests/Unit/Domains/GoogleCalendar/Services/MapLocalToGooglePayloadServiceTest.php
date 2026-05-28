<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Services\MapLocalToGooglePayloadService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapLocalToGooglePayloadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_timed_event_with_rfc3339_datetime(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'title' => 'Coffee',
            'description' => 'with the team',
            'location' => 'Cafe',
            'start_at' => CarbonImmutable::parse('2026-06-01 10:00:00'),
            'end_at' => CarbonImmutable::parse('2026-06-01 11:00:00'),
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
        ]);

        $payload = (new MapLocalToGooglePayloadService)->execute($event);

        $this->assertSame('Coffee', $payload['summary']);
        $this->assertSame('with the team', $payload['description']);
        $this->assertSame('Cafe', $payload['location']);
        $this->assertArrayHasKey('dateTime', $payload['start']);
        $this->assertArrayHasKey('dateTime', $payload['end']);
        $this->assertSame('7', $payload['colorId']);
        $this->assertSame('event', $payload['extendedProperties']['private']['dashyType']);
        $this->assertSame((string) $event->id, $payload['extendedProperties']['private']['dashyLocalId']);
    }

    public function test_maps_all_day_event_with_exclusive_end_date(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser($user)->create([
            'title' => 'Conference',
            'start_at' => CarbonImmutable::parse('2026-06-10 00:00:00'),
            'end_at' => CarbonImmutable::parse('2026-06-12 00:00:00'),
            'is_all_day' => true,
            'color' => EventColor::Shilo->value,
        ]);

        $payload = (new MapLocalToGooglePayloadService)->execute($event);

        $this->assertArrayHasKey('date', $payload['start']);
        $this->assertArrayHasKey('date', $payload['end']);
        $this->assertSame('2026-06-10', $payload['start']['date']);
        $this->assertSame('2026-06-13', $payload['end']['date']); // exclusive: end + 1 day
        $this->assertSame('4', $payload['colorId']);
    }

    public function test_maps_each_color_to_a_google_color_id(): void
    {
        $user = User::factory()->create();
        $mapper = new MapLocalToGooglePayloadService;

        $expected = [
            'danube' => '7',
            'torea' => '9',
            'shilo' => '4',
            'success' => '10',
            'warning' => '5',
            'error' => '11',
        ];

        foreach ($expected as $colorValue => $googleColorId) {
            $event = Event::factory()->forUser($user)->create(['color' => $colorValue]);
            $this->assertSame($googleColorId, $mapper->execute($event)['colorId']);
        }
    }

    public function test_maps_task_to_event_with_task_extended_property(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 0]);
        $task = Task::factory()->forProject($project, $status)->create([
            'created_by_user_id' => $user->id,
            'name' => 'Write proposal',
            'description' => 'Q3 roadmap',
            'start_date' => CarbonImmutable::parse('2026-06-15 09:00:00'),
            'end_date' => CarbonImmutable::parse('2026-06-15 10:30:00'),
        ]);

        $payload = (new MapLocalToGooglePayloadService)->execute($task);

        $this->assertSame('Write proposal', $payload['summary']);
        $this->assertSame('Q3 roadmap', $payload['description']);
        $this->assertSame('task', $payload['extendedProperties']['private']['dashyType']);
        $this->assertSame((string) $task->id, $payload['extendedProperties']['private']['dashyLocalId']);
    }

    public function test_task_without_end_date_defaults_to_one_hour_after_start(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 0]);
        $task = Task::factory()->forProject($project, $status)->create([
            'created_by_user_id' => $user->id,
            'start_date' => CarbonImmutable::parse('2026-06-15 09:00:00'),
            'end_date' => null,
        ]);

        $payload = (new MapLocalToGooglePayloadService)->execute($task);

        // Expect end to be one hour after start.
        $this->assertStringContainsString('2026-06-15T10:00', $payload['end']['dateTime']);
    }
}
