<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\AvatarService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AvatarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function service(): AvatarService
    {
        return app(AvatarService::class);
    }

    public function test_upload_stores_image_and_updates_avatar_url(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $file = UploadedFile::fake()->image('me.jpg', 200, 200);

        $updated = $this->service()->upload($user, $file);

        $this->assertNotNull($updated->avatar);
        $this->assertStringContainsString("avatars/{$user->id}/", $updated->avatar);

        $publicPrefix = Storage::disk('public')->url('');
        $relative = ltrim(substr($updated->avatar, strlen($publicPrefix)), '/');
        Storage::disk('public')->assertExists($relative);
    }

    public function test_upload_rejects_non_image_file(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->expectException(ValidationException::class);

        $this->service()->upload($user, $file);
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $file = UploadedFile::fake()->image('huge.jpg')->size(2049);

        $this->expectException(ValidationException::class);

        $this->service()->upload($user, $file);
    }

    public function test_upload_deletes_previous_local_avatar(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $first = $this->service()->upload($user, UploadedFile::fake()->image('first.jpg'));

        $publicPrefix = Storage::disk('public')->url('');
        $firstRelative = ltrim(substr($first->avatar, strlen($publicPrefix)), '/');

        $this->service()->upload($user->fresh(), UploadedFile::fake()->image('second.jpg'));

        Storage::disk('public')->assertMissing($firstRelative);
    }

    public function test_upload_does_not_delete_remote_google_avatar(): void
    {
        $googleUrl = 'https://lh3.googleusercontent.com/a/avatar';
        $user = User::factory()->create(['avatar' => $googleUrl]);

        // Just shouldn't throw — there's nothing on the local disk to remove.
        $this->service()->upload($user, UploadedFile::fake()->image('mine.jpg'));

        $this->assertStringStartsNotWith($googleUrl, $user->fresh()->avatar);
    }

    public function test_remove_clears_column_and_deletes_local_file(): void
    {
        $user = User::factory()->create(['avatar' => null]);
        $uploaded = $this->service()->upload($user, UploadedFile::fake()->image('me.jpg'));

        $publicPrefix = Storage::disk('public')->url('');
        $relative = ltrim(substr($uploaded->avatar, strlen($publicPrefix)), '/');
        Storage::disk('public')->assertExists($relative);

        $removed = $this->service()->remove($user->fresh());

        $this->assertNull($removed->avatar);
        Storage::disk('public')->assertMissing($relative);
    }

    public function test_remove_with_remote_avatar_just_clears_column(): void
    {
        $user = User::factory()->create(['avatar' => 'https://lh3.googleusercontent.com/a/x']);

        $removed = $this->service()->remove($user);

        $this->assertNull($removed->avatar);
    }
}
