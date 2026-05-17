<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\TeamLogoService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeamLogoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_logo(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->makeTeam();
        $file = UploadedFile::fake()->image('logo.png', 256, 256);

        $updated = app(TeamLogoService::class)->upload($owner, $team, $file);

        $this->assertNotNull($updated->logo);
        $this->assertStringContainsString("team-logos/{$team->id}/", $updated->logo);
    }

    public function test_member_cannot_upload(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $file = UploadedFile::fake()->image('logo.png');

        $this->expectException(AuthorizationException::class);

        app(TeamLogoService::class)->upload($member, $team, $file);
    }

    public function test_non_image_is_rejected(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->makeTeam();
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $this->expectException(ValidationException::class);

        app(TeamLogoService::class)->upload($owner, $team, $file);
    }

    public function test_oversized_image_is_rejected(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->makeTeam();
        $file = UploadedFile::fake()->image('big.png')->size(3000);

        $this->expectException(ValidationException::class);

        app(TeamLogoService::class)->upload($owner, $team, $file);
    }

    public function test_uploading_replaces_old_local_logo(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->makeTeam();
        $service = app(TeamLogoService::class);

        $service->upload($owner, $team, UploadedFile::fake()->image('one.png'));
        $oldRelative = $this->relativePathFrom($team->fresh()->logo);
        Storage::disk('public')->assertExists($oldRelative);

        $service->upload($owner, $team, UploadedFile::fake()->image('two.png'));
        $newRelative = $this->relativePathFrom($team->fresh()->logo);

        $this->assertNotEquals($oldRelative, $newRelative);
        Storage::disk('public')->assertMissing($oldRelative);
        Storage::disk('public')->assertExists($newRelative);
    }

    public function test_owner_can_remove_logo(): void
    {
        Storage::fake('public');
        [$owner, $team] = $this->makeTeam();
        $uploaded = app(TeamLogoService::class)->upload(
            $owner,
            $team,
            UploadedFile::fake()->image('logo.png'),
        );
        $relative = $this->relativePathFrom($uploaded->logo);

        $cleared = app(TeamLogoService::class)->remove($owner, $team);

        $this->assertNull($cleared->logo);
        Storage::disk('public')->assertMissing($relative);
    }

    public function test_member_cannot_remove(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['logo' => 'http://example.com/x.png']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(TeamLogoService::class)->remove($member, $team);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeam(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }

    private function relativePathFrom(string $url): string
    {
        $publicPrefix = Storage::disk('public')->url('');

        return ltrim(substr($url, strlen($publicPrefix)), '/');
    }
}
