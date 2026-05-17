<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\UpdateProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private function ownerProject(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'Original',
            'description' => 'Original desc',
        ]);

        return [$owner, $team, $project];
    }

    public function test_owner_can_update_name_and_description(): void
    {
        [$owner, , $project] = $this->ownerProject();

        $updated = app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => 'Renamed', 'description' => 'New desc'],
        );

        $this->assertSame('Renamed', $updated->name);
        $this->assertSame('New desc', $updated->description);
    }

    public function test_member_cannot_update(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Stay']);

        $this->expectException(AuthorizationException::class);

        try {
            app(UpdateProjectService::class)->execute($member, $project->id, ['name' => 'Hacked']);
        } finally {
            $this->assertSame('Stay', $project->fresh()->name);
        }
    }

    public function test_404_when_project_missing(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(UpdateProjectService::class)->execute($user, 99999, ['name' => 'X']);
    }

    public function test_name_is_required(): void
    {
        [$owner, , $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(UpdateProjectService::class)->execute($owner, $project->id, ['name' => '']);
    }

    public function test_name_max_length_is_eighty(): void
    {
        [$owner, , $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => str_repeat('a', 81)],
        );
    }

    public function test_description_max_length_is_two_thousand(): void
    {
        [$owner, , $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => 'OK', 'description' => str_repeat('a', 2001)],
        );
    }

    public function test_logo_must_be_image(): void
    {
        Storage::fake('public');
        [$owner, , $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => 'OK'],
            UploadedFile::fake()->create('document.pdf', 50, 'application/pdf'),
        );
    }

    public function test_uploads_new_logo_and_deletes_old_local_logo(): void
    {
        Storage::fake('public');
        [$owner, $team, $project] = $this->ownerProject();

        // Seed an existing local logo on the project.
        $oldPath = UploadedFile::fake()->image('old.png')
            ->storePublicly("project-logos/{$team->id}", 'public');
        $project->forceFill(['logo' => Storage::disk('public')->url($oldPath)])->save();
        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        $updated = app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => 'OK'],
            UploadedFile::fake()->image('new.png'),
        );

        $this->assertNotNull($updated->logo);
        $this->assertNotSame(Storage::disk('public')->url($oldPath), $updated->logo);
        $this->assertFalse(Storage::disk('public')->exists($oldPath), 'Old logo file should be deleted.');
        $this->assertCount(1, Storage::disk('public')->files("project-logos/{$team->id}"));
    }

    public function test_keeps_existing_logo_when_no_new_file_uploaded(): void
    {
        Storage::fake('public');
        [$owner, , $project] = $this->ownerProject();
        $project->forceFill(['logo' => 'https://kept.example.test/x.png'])->save();

        $updated = app(UpdateProjectService::class)->execute(
            $owner,
            $project->id,
            ['name' => 'Renamed only'],
        );

        $this->assertSame('Renamed only', $updated->name);
        $this->assertSame('https://kept.example.test/x.png', $updated->logo);
    }

    public function test_db_failure_after_logo_upload_cleans_up_orphan_file(): void
    {
        Storage::fake('public');
        [$owner, $team, $project] = $this->ownerProject();

        $mock = \Mockery::mock(\App\Domains\Projects\Actions\UpdateProjectAction::class);
        $mock->shouldReceive('execute')->once()->andThrow(new \RuntimeException('db boom'));
        $this->instance(\App\Domains\Projects\Actions\UpdateProjectAction::class, $mock);

        try {
            app(UpdateProjectService::class)->execute(
                $owner,
                $project->id,
                ['name' => 'OK'],
                UploadedFile::fake()->image('new.png'),
            );
            $this->fail('Expected throw');
        } catch (\RuntimeException) {
            // Expected.
        }

        $this->assertSame(
            [],
            Storage::disk('public')->files("project-logos/{$team->id}"),
        );
    }
}
