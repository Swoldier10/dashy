<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\CreateTeamService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class CreateTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_team_and_attaches_creator_as_owner(): void
    {
        $user = User::factory()->create();

        $team = app(CreateTeamService::class)->execute($user, ['name' => 'Acme']);

        $this->assertSame('Acme', $team->name);
        $this->assertFalse($team->personal_team);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Owner->value,
        ]);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTeamService::class)->execute($user, ['name' => '']);
    }

    public function test_name_must_be_under_eighty_chars(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTeamService::class)->execute($user, ['name' => str_repeat('a', 81)]);
    }

    public function test_creates_team_with_logo_stored_under_team_path(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $team = app(CreateTeamService::class)->execute(
            $user,
            ['name' => 'Acme'],
            UploadedFile::fake()->image('logo.png', 256, 256),
        );

        $this->assertNotNull($team->logo);
        $this->assertStringContainsString("team-logos/{$team->id}/", $team->logo);
        $this->assertCount(1, Storage::disk('public')->allFiles("team-logos/{$team->id}"));
    }

    public function test_creates_team_without_logo_leaves_logo_null(): void
    {
        $user = User::factory()->create();

        $team = app(CreateTeamService::class)->execute($user, ['name' => 'Acme']);

        $this->assertNull($team->logo);
    }

    public function test_rejects_non_image_logo_and_persists_nothing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        try {
            app(CreateTeamService::class)->execute(
                $user,
                ['name' => 'Acme'],
                UploadedFile::fake()->create('virus.exe', 100),
            );
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException) {
            // Expected — the file is rejected before anything is written.
        }

        $this->assertSame(0, Team::query()->count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_rejects_oversized_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTeamService::class)->execute(
            $user,
            ['name' => 'Acme'],
            UploadedFile::fake()->image('big.png')->size(3000),
        );
    }

    public function test_deletes_stored_logo_when_transaction_fails(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->mock(UpdateTeamAction::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new RuntimeException('boom'));
        });

        try {
            app(CreateTeamService::class)->execute(
                $user,
                ['name' => 'Acme'],
                UploadedFile::fake()->image('logo.png'),
            );
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException) {
            // Expected — the transaction rolled back.
        }

        $this->assertSame(0, Team::query()->count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }
}
