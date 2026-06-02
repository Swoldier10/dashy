<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\FindLatestUserMessageAttachmentsAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindLatestUserMessageAttachmentsActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): FindLatestUserMessageAttachmentsAction
    {
        return new FindLatestUserMessageAttachmentsAction;
    }

    private function chat(): Chat
    {
        return Chat::create(['user_id' => User::factory()->create()->id]);
    }

    private function image(string $path): array
    {
        return ['type' => 'image', 'path' => $path, 'url' => 'https://t/'.$path, 'mime' => 'image/png', 'name' => basename($path)];
    }

    public function test_returns_attachments_of_the_newest_message_that_has_them(): void
    {
        $chat = $this->chat();
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'old', 'attachments' => [$this->image('a/old.png')]]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'new', 'attachments' => [$this->image('a/new.png')]]);

        $attachments = $this->action()->execute($chat);

        $this->assertCount(1, $attachments);
        $this->assertSame('a/new.png', $attachments[0]['path']);
    }

    public function test_scans_back_past_a_text_only_message_with_null_attachments(): void
    {
        $chat = $this->chat();
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'img', 'attachments' => [$this->image('a/img.png')]]);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'which project?']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'Folienzuschnitt']);

        $attachments = $this->action()->execute($chat);

        $this->assertCount(1, $attachments);
        $this->assertSame('a/img.png', $attachments[0]['path']);
    }

    public function test_skips_a_message_with_an_explicit_empty_attachments_array(): void
    {
        $chat = $this->chat();
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'img', 'attachments' => [$this->image('a/img.png')]]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'empty', 'attachments' => []]);

        $attachments = $this->action()->execute($chat);

        $this->assertCount(1, $attachments);
        $this->assertSame('a/img.png', $attachments[0]['path']);
    }

    public function test_ignores_assistant_messages_with_attachments(): void
    {
        $chat = $this->chat();
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'a', 'attachments' => [$this->image('a/assistant.png')]]);

        $this->assertSame([], $this->action()->execute($chat));
    }

    public function test_returns_empty_array_when_no_message_has_attachments(): void
    {
        $chat = $this->chat();
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'hi']);

        $this->assertSame([], $this->action()->execute($chat));
    }
}
