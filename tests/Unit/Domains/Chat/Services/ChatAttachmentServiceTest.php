<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Services\AudioTranscriptionService;
use App\Domains\Chat\Services\ChatAttachmentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ChatAttachmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();
    }

    public function test_stores_image_under_chat_path(): void
    {
        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->image('photo.png', 600, 400);

        $payload = $service->storeImage($user, 42, $file);

        $this->assertSame('image', $payload['type']);
        $this->assertStringStartsWith("chat-attachments/{$user->id}/42/", $payload['path']);
        $this->assertSame('photo.png', $payload['name']);
        $this->assertNull($payload['transcript']);
        Storage::disk('public')->assertExists($payload['path']);
    }

    public function test_image_validation_rejects_non_image(): void
    {
        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $this->expectException(ValidationException::class);

        $service->storeImage($user, 1, $file);
    }

    public function test_image_validation_rejects_oversize(): void
    {
        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->image('big.png')->size(11_000);

        $this->expectException(ValidationException::class);

        $service->storeImage($user, 1, $file);
    }

    public function test_stores_audio_under_chat_path(): void
    {
        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->createWithContent(
            'voice.webm',
            'fake-opus-bytes',
        )->mimeType('audio/webm');

        $payload = $service->storeAudio($user, 7, $file);

        $this->assertSame('audio', $payload['type']);
        $this->assertStringStartsWith("chat-attachments/{$user->id}/7/", $payload['path']);
        Storage::disk('public')->assertExists($payload['path']);
    }

    public function test_audio_validation_rejects_non_audio_mime(): void
    {
        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->expectException(ValidationException::class);

        $service->storeAudio($user, 1, $file);
    }

    public function test_audio_attachment_includes_transcript_when_api_returns_text(): void
    {
        config(['services.openai.api_key' => 'sk-test']);
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response(['text' => 'Hello world'], 200),
        ]);

        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->createWithContent(
            'voice.webm',
            'opus-bytes',
        )->mimeType('audio/webm');

        $payload = $service->storeAudio($user, 1, $file);

        $this->assertSame('Hello world', $payload['transcript']);
    }

    public function test_audio_attachment_has_null_transcript_when_api_key_missing(): void
    {
        config(['services.openai.api_key' => null]);

        $user = User::factory()->create();
        $service = $this->makeService();
        $file = UploadedFile::fake()->createWithContent(
            'voice.webm',
            'opus-bytes',
        )->mimeType('audio/webm');

        $payload = $service->storeAudio($user, 1, $file);

        $this->assertNull($payload['transcript']);
    }

    private function makeService(): ChatAttachmentService
    {
        return new ChatAttachmentService(new AudioTranscriptionService);
    }
}
