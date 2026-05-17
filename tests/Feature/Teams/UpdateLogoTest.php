<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_a_logo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newLogo', UploadedFile::fake()->image('logo.png'))
            ->assertHasNoErrors();

        $this->assertNotNull($team->fresh()->logo);
    }

    public function test_owner_can_remove_logo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newLogo', UploadedFile::fake()->image('logo.png'))
            ->call('removeLogo')
            ->assertHasNoErrors();

        $this->assertNull($team->fresh()->logo);
    }

    public function test_member_cannot_upload(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newLogo', UploadedFile::fake()->image('logo.png'))
            ->assertForbidden();

        $this->assertNull($team->fresh()->logo);
    }

    public function test_invalid_file_shows_error(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('newLogo', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->assertHasErrors();

        $this->assertNull($team->fresh()->logo);
    }
}
