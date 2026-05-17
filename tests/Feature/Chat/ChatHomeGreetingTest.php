<?php

namespace Tests\Feature\Chat;

use App\Domains\Calendar\Models\Event;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatHomeGreetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pin "now" so the date pill and greeting are deterministic. Use a
        // mid-evening time so the greeting reads "Good evening".
        Carbon::setTestNow('2026-05-16 19:42:00');
    }

    private function connectCodex(User $user): void
    {
        CodexConnection::create([
            'user_id' => $user->id,
            'codex_user_id' => 'cx-'.$user->id,
            'access_token' => 'test-token',
            'refresh_token' => null,
            'access_token_expires_at' => now()->addHour(),
            'account_email' => $user->email,
            'account_name' => $user->name,
            'scope' => null,
        ]);
    }

    public function test_renders_date_pill_greeting_and_composer(): void
    {
        $user = User::factory()->create(['first_name' => 'Raul']);
        $this->connectCodex($user);
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSee('data-test="chat-date-pill"', escape: false);
        $response->assertSee('data-test="chat-greeting"', escape: false);
        $response->assertSee('data-test="chat-subtitle"', escape: false);
        $response->assertSeeText('SATURDAY, MAY 16');
        $response->assertSeeText('7:42 PM');
        $response->assertSeeText('Good evening, Raul');
    }

    public function test_subtitle_counts_tomorrows_meetings_and_tasks(): void
    {
        $user = User::factory()->create(['first_name' => 'Raul']);
        $this->connectCodex($user);

        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        // Tomorrow = 2026-05-17. Seed 2 events and 4 tasks.
        Event::factory()->forUser($user)->create([
            'title' => 'Standup',
            'start_at' => '2026-05-17 09:00:00',
            'end_at' => '2026-05-17 09:30:00',
        ]);
        Event::factory()->forUser($user)->create([
            'title' => 'Client call',
            'start_at' => '2026-05-17 14:00:00',
            'end_at' => '2026-05-17 15:00:00',
        ]);
        for ($i = 0; $i < 4; $i++) {
            Task::factory()->create([
                'project_id' => $project->id,
                'project_status_id' => $status->id,
                'name' => "Task {$i}",
                'end_date' => '2026-05-17 12:00:00',
            ]);
        }

        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('2 meetings');
        $response->assertSeeText('4 tasks');
    }

    public function test_morning_greeting_branch(): void
    {
        Carbon::setTestNow('2026-05-16 08:30:00');

        $user = User::factory()->create(['first_name' => 'Raul']);
        $this->connectCodex($user);
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('Good morning, Raul');
    }

    public function test_afternoon_greeting_branch(): void
    {
        Carbon::setTestNow('2026-05-16 14:15:00');

        $user = User::factory()->create(['first_name' => 'Raul']);
        $this->connectCodex($user);
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('Good afternoon, Raul');
    }

    public function test_empty_tomorrow_shows_zero_counts(): void
    {
        $user = User::factory()->create(['first_name' => 'Raul']);
        $this->connectCodex($user);
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('no meetings');
        $response->assertSeeText('no tasks');
    }
}
