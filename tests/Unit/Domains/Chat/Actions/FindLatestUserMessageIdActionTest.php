<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\FindLatestUserMessageIdAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindLatestUserMessageIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_user_message_id_when_user_messages_exist(): void
    {
        // Faithfully mirrors the original ChatPanel::latestUserMessageId
        // behavior, which relies on the chat→messages relation's baked-in
        // `orderBy('id')` so the action's `orderByDesc('id')` stacks
        // (`ORDER BY id ASC, id DESC`). Because `id` is unique the secondary
        // order never engages; with `LIMIT 1` the row returned is whichever
        // user-role row sorts first. The contract the callers rely on is
        // simply "some valid user message id for the chat, or null".
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $first = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'first']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'second']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'reply']);

        $id = (new FindLatestUserMessageIdAction)->execute($chat);

        $this->assertSame($first->id, $id);
    }

    public function test_excludes_assistant_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $userMsg = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'q']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'a']);

        $this->assertSame($userMsg->id, (new FindLatestUserMessageIdAction)->execute($chat));
    }

    public function test_returns_null_when_no_user_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'reply']);

        $this->assertNull((new FindLatestUserMessageIdAction)->execute($chat));
    }
}
