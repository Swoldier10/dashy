<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\CountChatMessagesAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountChatMessagesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_messages_for_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'a']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'b']);

        $other = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $other->id, 'role' => 'user', 'content' => 'c']);

        $this->assertSame(2, (new CountChatMessagesAction)->execute($chat));
    }

    public function test_returns_zero_when_no_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $this->assertSame(0, (new CountChatMessagesAction)->execute($chat));
    }
}
