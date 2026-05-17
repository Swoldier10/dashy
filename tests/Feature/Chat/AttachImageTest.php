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

class AttachImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Http::preventStrayRequests();
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

    public function test_uploading_image_adds_to_persisted_attachments(): void
    {
        $this->actingAs($this->userWithCodex());

        $component = Livewire::test('chat.chat-panel')
            ->set('imageUploads', [UploadedFile::fake()->image('photo.png', 200, 200)])
            ->assertHasNoErrors();

        $attachments = $component->get('persistedAttachments');
        $this->assertCount(1, $attachments);
        $this->assertSame('image', $attachments[0]['type']);
        Storage::disk('public')->assertExists($attachments[0]['path']);
    }

    public function test_remove_attachment_clears_thumbnail_and_deletes_file(): void
    {
        $this->actingAs($this->userWithCodex());

        $component = Livewire::test('chat.chat-panel')
            ->set('imageUploads', [UploadedFile::fake()->image('photo.png')]);

        $path = $component->get('persistedAttachments.0.path');
        Storage::disk('public')->assertExists($path);

        $component->call('removeAttachment', 0)
            ->assertSet('persistedAttachments', []);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_send_message_with_image_only_creates_chat_and_user_message(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('I see a picture.');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('imageUploads', [UploadedFile::fake()->image('photo.png')])
            ->set('message', '')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->call('processAssistantReply');

        $this->assertSame(1, Chat::count());
        $msg = Message::where('role', 'user')->firstOrFail();
        $this->assertNotEmpty($msg->attachments);
        $this->assertSame('image', $msg->attachments[0]['type']);
    }

    public function test_send_message_persists_attachments_json_for_thread_render(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('imageUploads', [UploadedFile::fake()->image('photo.png')])
            ->set('message', 'What is this?')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $msg = Message::where('role', 'user')->firstOrFail();
        $this->assertSame('What is this?', $msg->content);
        $this->assertIsArray($msg->attachments);
        $this->assertSame('image', $msg->attachments[0]['type']);
    }

    public function test_image_data_url_is_sent_to_codex_streamer(): void
    {
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
            ->set('imageUploads', [UploadedFile::fake()->image('photo.png')])
            ->set('message', 'describe')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $this->assertNotEmpty($captured);
        $userMessage = collect($captured)->firstWhere('role', 'user');
        $this->assertNotNull($userMessage);

        $imageBlocks = collect($userMessage['content'])->where('type', 'input_image')->values()->all();
        $this->assertNotEmpty($imageBlocks);
        $this->assertStringStartsWith('data:image/', $imageBlocks[0]['image_url']);
    }
}
