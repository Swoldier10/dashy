<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\FindLatestUserMessageImagesService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindLatestUserMessageImagesServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): FindLatestUserMessageImagesService
    {
        return app(FindLatestUserMessageImagesService::class);
    }

    private function chatForUser(): Chat
    {
        $user = User::factory()->create();

        return Chat::create(['user_id' => $user->id, 'title' => 'c']);
    }

    public function test_returns_images_from_the_latest_user_message_not_an_earlier_one(): void
    {
        $chat = $this->chatForUser();
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'old',
            'attachments' => [['type' => 'image', 'path' => 'a/old.png', 'url' => 'https://t/old.png', 'mime' => 'image/png', 'name' => 'old.png']],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'new',
            'attachments' => [
                ['type' => 'image', 'path' => 'a/first.png', 'url' => 'https://t/first.png', 'mime' => 'image/png', 'name' => 'first.png'],
                ['type' => 'image', 'path' => 'a/second.png', 'url' => 'https://t/second.png', 'mime' => 'image/png', 'name' => 'second.png'],
            ],
        ]);

        $images = $this->service()->execute($chat);

        $this->assertCount(2, $images);
        $this->assertSame('a/first.png', $images[0]['path']);
        $this->assertSame('a/second.png', $images[1]['path']);
    }

    public function test_scans_back_to_image_message_when_latest_user_message_is_text_only(): void
    {
        $chat = $this->chatForUser();
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'create a task based on this',
            'attachments' => [['type' => 'image', 'path' => 'a/shot.png', 'url' => 'https://t/shot.png', 'mime' => 'image/png', 'name' => 'shot.png']],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => 'Which project?',
        ]);
        // Latest user message is a plain text reply with no attachments (null).
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'Folienzuschnitt',
        ]);

        $images = $this->service()->execute($chat);

        $this->assertCount(1, $images);
        $this->assertSame('a/shot.png', $images[0]['path']);
    }

    public function test_skips_an_intermediate_empty_attachments_message(): void
    {
        $chat = $this->chatForUser();
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'with image',
            'attachments' => [['type' => 'image', 'path' => 'a/img.png', 'url' => 'https://t/img.png', 'mime' => 'image/png', 'name' => 'img.png']],
        ]);
        // A later user message persisted with an explicit empty array (non-null).
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'no attachment here',
            'attachments' => [],
        ]);

        $images = $this->service()->execute($chat);

        $this->assertCount(1, $images);
        $this->assertSame('a/img.png', $images[0]['path']);
    }

    public function test_filters_out_non_image_and_malformed_attachments(): void
    {
        $chat = $this->chatForUser();
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'mixed',
            'attachments' => [
                ['type' => 'audio', 'path' => 'a/v.webm', 'url' => 'https://t/v.webm'],
                ['type' => 'image', 'path' => 'a/ok.png', 'url' => 'https://t/ok.png'],
                ['type' => 'image', 'name' => 'no-path-or-url.png'],
            ],
        ]);

        $images = $this->service()->execute($chat);

        $this->assertCount(1, $images);
        $this->assertSame('a/ok.png', $images[0]['path']);
        $this->assertNull($images[0]['mime']);
    }

    public function test_returns_empty_array_when_there_is_no_user_message(): void
    {
        $chat = $this->chatForUser();

        $this->assertSame([], $this->service()->execute($chat));
    }
}
