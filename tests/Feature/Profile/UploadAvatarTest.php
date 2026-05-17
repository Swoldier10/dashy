<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UploadAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_avatar_upload_persists_url(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('newAvatar', UploadedFile::fake()->image('me.jpg', 200, 200))
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertStringContainsString("avatars/{$user->id}/", $user->avatar);
    }

    public function test_avatar_upload_rejects_non_image(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('newAvatar', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->assertHasErrors();

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_avatar_can_be_removed(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $this->actingAs($user);

        // Upload then remove.
        Livewire::test('settings.profile-section')
            ->set('newAvatar', UploadedFile::fake()->image('me.jpg'))
            ->call('removeAvatar')
            ->assertHasNoErrors();

        $this->assertNull($user->fresh()->avatar);
    }
}
