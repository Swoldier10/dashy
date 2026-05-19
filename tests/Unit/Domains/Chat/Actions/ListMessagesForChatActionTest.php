<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\ListMessagesForChatAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMessagesForChatActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_messages_ordered_by_id(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $a = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'first']);
        $b = Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'second']);

        $messages = (new ListMessagesForChatAction)->execute($chat);

        $this->assertCount(2, $messages);
        $this->assertSame($a->id, $messages[0]->id);
        $this->assertSame($b->id, $messages[1]->id);
    }

    public function test_returns_empty_when_no_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $this->assertCount(0, (new ListMessagesForChatAction)->execute($chat));
    }

    public function test_respects_column_subset(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'hi']);

        $messages = (new ListMessagesForChatAction)->execute($chat, ['id', 'role']);

        $this->assertSame('user', $messages->first()->role->value);
        $this->assertNull($messages->first()->content);
    }
}
