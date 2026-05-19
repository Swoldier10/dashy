<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\ListChatAttachmentPathsAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListChatAttachmentPathsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_flat_list_of_attachment_paths(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => '',
            'attachments' => [
                ['type' => 'image', 'path' => 'chats/a.png', 'url' => '/a'],
                ['type' => 'image', 'path' => 'chats/b.png', 'url' => '/b'],
            ],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'no attachments',
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => '',
            'attachments' => [
                ['type' => 'audio', 'path' => 'chats/v.mp3', 'url' => '/v'],
            ],
        ]);

        $paths = (new ListChatAttachmentPathsAction)->execute($chat);

        $this->assertSame(['chats/a.png', 'chats/b.png', 'chats/v.mp3'], $paths);
    }

    public function test_returns_empty_when_no_attachments(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'plain']);

        $this->assertSame([], (new ListChatAttachmentPathsAction)->execute($chat));
    }
}
