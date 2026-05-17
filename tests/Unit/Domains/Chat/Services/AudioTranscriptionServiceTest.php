<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Services\AudioTranscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AudioTranscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_returns_null_when_api_key_is_missing(): void
    {
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();

        $service = new AudioTranscriptionService;

        $this->assertNull($service->transcribe('whatever.webm'));
    }

    public function test_returns_text_from_successful_response_and_sends_bearer_token(): void
    {
        config(['services.openai.api_key' => 'sk-test-key']);
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response(['text' => 'Hello there'], 200),
        ]);

        Storage::disk('public')->put('voice/abc.webm', 'fake-audio-bytes');

        $service = new AudioTranscriptionService;
        $result = $service->transcribe('voice/abc.webm');

        $this->assertSame('Hello there', $result);
        Http::assertSent(function (Request $request): bool {
            return $request->hasHeader('Authorization', 'Bearer sk-test-key')
                && str_contains($request->url(), 'api.openai.com/v1/audio/transcriptions');
        });
    }

    public function test_returns_null_on_non_2xx_response(): void
    {
        config(['services.openai.api_key' => 'sk-test-key']);
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response(['error' => 'oops'], 500),
        ]);

        Storage::disk('public')->put('voice/abc.webm', 'fake-audio-bytes');

        $service = new AudioTranscriptionService;

        $this->assertNull($service->transcribe('voice/abc.webm'));
    }

    public function test_returns_null_when_file_cannot_be_read(): void
    {
        config(['services.openai.api_key' => 'sk-test-key']);
        Http::preventStrayRequests();

        $service = new AudioTranscriptionService;

        $this->assertNull($service->transcribe('voice/missing.webm'));
    }

    public function test_returns_null_when_response_text_is_empty(): void
    {
        config(['services.openai.api_key' => 'sk-test-key']);
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response(['text' => '   '], 200),
        ]);
        Storage::disk('public')->put('voice/abc.webm', 'bytes');

        $service = new AudioTranscriptionService;

        $this->assertNull($service->transcribe('voice/abc.webm'));
    }
}
