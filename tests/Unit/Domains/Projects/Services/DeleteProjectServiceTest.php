<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\DeleteProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private function teamWithUser(string $role): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => $role]);

        return [$user, $team];
    }

    public function test_owner_can_delete(): void
    {
        [$owner, $team] = $this->teamWithUser(TeamRole::Owner->value);
        $project = Project::factory()->create(['team_id' => $team->id]);

        app(DeleteProjectService::class)->execute($owner, $project->id);

        $this->assertSame(0, Project::count());
    }

    public function test_member_cannot_delete(): void
    {
        [$member, $team] = $this->teamWithUser(TeamRole::Member->value);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->expectException(AuthorizationException::class);

        try {
            app(DeleteProjectService::class)->execute($member, $project->id);
        } finally {
            $this->assertSame(1, Project::count());
        }
    }

    public function test_non_team_user_cannot_delete(): void
    {
        $stranger = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->expectException(AuthorizationException::class);

        try {
            app(DeleteProjectService::class)->execute($stranger, $project->id);
        } finally {
            $this->assertSame(1, Project::count());
        }
    }

    public function test_404_when_project_missing(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(DeleteProjectService::class)->execute($user, 99999);
    }

    public function test_local_logo_file_is_deleted_on_success(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->teamWithUser(TeamRole::Owner->value);

        $path = UploadedFile::fake()->image('logo.png')
            ->storePublicly("project-logos/{$team->id}", 'public');
        $url = Storage::disk('public')->url($path);
        $project = Project::factory()->create(['team_id' => $team->id, 'logo' => $url]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        app(DeleteProjectService::class)->execute($owner, $project->id);

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_remote_logo_url_is_left_alone(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->teamWithUser(TeamRole::Owner->value);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'logo' => 'https://cdn.example.com/elsewhere/logo.png',
        ]);

        // No exception, no Storage interaction with that URL.
        app(DeleteProjectService::class)->execute($owner, $project->id);

        $this->assertSame(0, Project::count());
    }
}
