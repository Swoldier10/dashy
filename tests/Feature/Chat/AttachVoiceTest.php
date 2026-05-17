<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AttachVoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function userWithCodex(): User
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);

        return $user;
    }

    private function fakeWebmFile(): UploadedFile
    {
        return UploadedFile::fake()
            ->createWithContent('voice.webm', 'fake-opus-bytes')
            ->mimeType('audio/webm');
    }

    public function test_voice_upload_adds_to_persisted_attachments(): void
    {
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();
        $this->actingAs($this->userWithCodex());

        $component = Livewire::test('chat.chat-panel')
            ->set('voiceUpload', $this->fakeWebmFile())
            ->assertHasNoErrors();

        $attachments = $component->get('persistedAttachments');
        $this->assertCount(1, $attachments);
        $this->assertSame('audio', $attachments[0]['type']);
        $this->assertNull($attachments[0]['transcript']);
        Storage::disk('public')->assertExists($attachments[0]['path']);
    }

    public function test_voice_works_without_api_key_but_transcript_stays_null(): void
    {
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $captured = [];
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function ($connection, array $messages) use (&$captured) {
            $captured = $messages;
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('voiceUpload', $this->fakeWebmFile())
            ->set('message', '')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $msg = Message::where('role', 'user')->firstOrFail();
        $this->assertSame('audio', $msg->attachments[0]['type']);
        $this->assertNull($msg->attachments[0]['transcript']);

        // Without a transcript, the voice block is NOT injected into LLM input.
        $userMessage = collect($captured)->firstWhere('role', 'user');
        $this->assertNotNull($userMessage);
        $textBlock = collect($userMessage['content'])->firstWhere('type', 'input_text');
        $this->assertNotNull($textBlock);
        $this->assertStringNotContainsString('[Voice note]', $textBlock['text']);
    }

    public function test_transcript_is_merged_into_user_content_for_codex(): void
    {
        config(['services.openai.api_key' => 'sk-test']);
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response(['text' => 'Hi there'], 200),
            // The EmbeddingObserver dispatches once the api key is set;
            // return a benign vector so the embed job upserts cleanly.
            'https://api.openai.com/v1/embeddings' => Http::response([
                'data' => [['embedding' => array_fill(0, 8, 0.1)]],
            ], 200),
        ]);
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $captured = [];
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function ($connection, array $messages) use (&$captured) {
            $captured = $messages;
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('voiceUpload', $this->fakeWebmFile())
            ->set('message', '')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $userMessage = collect($captured)->firstWhere('role', 'user');
        $this->assertNotNull($userMessage);
        $textBlock = collect($userMessage['content'])->firstWhere('type', 'input_text');
        $this->assertNotNull($textBlock);
        $this->assertStringContainsString('[Voice note] Hi there', $textBlock['text']);
    }

    public function test_audio_binary_is_not_sent_to_codex(): void
    {
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $captured = [];
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function ($connection, array $messages) use (&$captured) {
            $captured = $messages;
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('voiceUpload', $this->fakeWebmFile())
            ->set('message', 'note')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $serialized = json_encode($captured);
        $this->assertStringNotContainsString('data:audio/', (string) $serialized);
        $userMessage = collect($captured)->firstWhere('role', 'user');
        $imageBlocks = collect($userMessage['content'])->where('type', 'input_image')->values()->all();
        $this->assertEmpty($imageBlocks);
    }

    public function test_chat_is_created_with_voice_message_seed_when_only_voice_present(): void
    {
        config(['services.openai.api_key' => null]);
        Http::preventStrayRequests();
        $this->actingAs($this->userWithCodex());

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('voiceUpload', $this->fakeWebmFile())
            ->set('message', '')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $this->assertSame(1, Chat::count());
        $this->assertSame('Voice message', Chat::first()->title);
    }
}
