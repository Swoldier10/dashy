<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\CreateProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private function teamWithMember(string $role = TeamRole::Member->value): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => $role]);

        return [$user, $team];
    }

    public function test_creates_project_for_team_member_without_logo(): void
    {
        [$user, $team] = $this->teamWithMember();

        $project = app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'New site', 'description' => 'A description'],
        );

        $this->assertSame('New site', $project->name);
        $this->assertSame('A description', $project->description);
        $this->assertSame($team->id, $project->team_id);
        $this->assertNull($project->logo);
    }

    public function test_creates_project_with_logo_and_stores_file(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->teamWithMember();

        $project = app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'Logo project'],
            UploadedFile::fake()->image('logo.png'),
        );

        $this->assertNotNull($project->logo);
        $this->assertStringContainsString("project-logos/{$team->id}/", $project->logo);

        // The file actually landed on the disk.
        $this->assertCount(
            1,
            Storage::disk('public')->files("project-logos/{$team->id}"),
        );
    }

    public function test_name_is_required(): void
    {
        [$user, $team] = $this->teamWithMember();

        $this->expectException(ValidationException::class);

        app(CreateProjectService::class)->execute($user, $team, ['name' => '']);
    }

    public function test_name_max_length_is_eighty(): void
    {
        [$user, $team] = $this->teamWithMember();

        $this->expectException(ValidationException::class);

        app(CreateProjectService::class)->execute($user, $team, ['name' => str_repeat('a', 81)]);
    }

    public function test_description_max_length_is_two_thousand(): void
    {
        [$user, $team] = $this->teamWithMember();

        $this->expectException(ValidationException::class);

        app(CreateProjectService::class)->execute($user, $team, [
            'name' => 'OK',
            'description' => str_repeat('a', 2001),
        ]);
    }

    public function test_logo_must_be_image(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->teamWithMember();

        $this->expectException(ValidationException::class);

        app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'Bad logo'],
            UploadedFile::fake()->create('document.pdf', 50, 'application/pdf'),
        );
    }

    public function test_logo_too_large_fails(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->teamWithMember();

        $this->expectException(ValidationException::class);

        app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'Big logo'],
            UploadedFile::fake()->image('big.png')->size(3000),
        );
    }

    public function test_non_member_cannot_create(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(CreateProjectService::class)->execute($user, $team, ['name' => 'Nope']);
    }

    public function test_owner_can_create(): void
    {
        [$user, $team] = $this->teamWithMember(TeamRole::Owner->value);

        $project = app(CreateProjectService::class)->execute($user, $team, ['name' => 'OK']);

        $this->assertSame('OK', $project->name);
    }

    public function test_db_failure_after_logo_upload_cleans_up_orphan_file(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->teamWithMember();

        $mock = \Mockery::mock(\App\Domains\Projects\Actions\CreateProjectAction::class);
        $mock->shouldReceive('execute')->once()->andThrow(new \RuntimeException('db boom'));
        $this->instance(\App\Domains\Projects\Actions\CreateProjectAction::class, $mock);

        try {
            app(CreateProjectService::class)->execute(
                $user,
                $team,
                ['name' => 'Doomed'],
                UploadedFile::fake()->image('logo.png'),
            );
            $this->fail('Expected the service to throw.');
        } catch (\RuntimeException $e) {
            $this->assertSame('db boom', $e->getMessage());
        }

        $this->assertSame(
            [],
            Storage::disk('public')->files("project-logos/{$team->id}"),
            'Orphan logo file should be cleaned up on rollback.',
        );
        $this->assertSame(0, Project::count());
    }

    public function test_creates_buffered_statuses_in_same_transaction(): void
    {
        [$user, $team] = $this->teamWithMember();

        $project = app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'With statuses'],
            null,
            [
                ['category' => 'not_started', 'name' => 'TODO'],
                ['category' => 'not_started', 'name' => 'BACKLOG'],
                ['category' => 'active', 'name' => 'IN PROGRESS'],
            ],
        );

        $statuses = $project->statuses()->get();
        $this->assertCount(3, $statuses);

        $notStarted = $statuses->where('category', \App\Domains\Projects\Enums\ProjectStatusCategory::NotStarted)->values();
        $this->assertSame(['TODO', 'BACKLOG'], $notStarted->pluck('name')->all());
        $this->assertSame([0, 1], $notStarted->pluck('position')->all());
    }

    public function test_accepts_pre_stored_file_via_test_mode_uploaded_file_and_copies_it(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->teamWithMember();

        // Pre-place an image on the public disk (like a chat attachment).
        $source = UploadedFile::fake()->image('original.png');
        $sourcePath = $source->storePublicly('chat-attachments/'.$user->id.'/_pending', 'public');

        // CreateProjectTool wraps the chat-stored path as a test-mode
        // UploadedFile so it can pass through this service unchanged.
        $logo = new UploadedFile(
            Storage::disk('public')->path($sourcePath),
            'logo.png',
            'image/png',
            null,
            true,
        );

        $project = app(CreateProjectService::class)->execute(
            $user,
            $team,
            ['name' => 'From attachment'],
            $logo,
        );

        $this->assertNotNull($project->logo);
        $this->assertStringContainsString("project-logos/{$team->id}/", $project->logo);

        // Copy, not move — source remains.
        $this->assertTrue(Storage::disk('public')->exists($sourcePath));
        $this->assertCount(
            1,
            Storage::disk('public')->files("project-logos/{$team->id}"),
        );
    }

    public function test_invalid_status_rolls_back_project_and_statuses(): void
    {
        [$user, $team] = $this->teamWithMember();

        try {
            app(CreateProjectService::class)->execute(
                $user,
                $team,
                ['name' => 'Should not persist'],
                null,
                [['category' => 'not_started', 'name' => '']],
            );
            $this->fail('Expected ValidationException.');
        } catch (\Illuminate\Validation\ValidationException) {
            // Expected.
        }

        $this->assertSame(0, Project::count());
        $this->assertSame(0, \App\Domains\Projects\Models\ProjectStatus::count());
    }
}
