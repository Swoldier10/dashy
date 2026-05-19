<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\ListPendingToolCallsForTurnAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPendingToolCallsForTurnActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_tool_call_messages_for_given_parent(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $parent = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'do it']);
        $otherParent = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'other']);

        Message::create([
            'chat_id' => $chat->id,
            'parent_user_message_id' => $parent->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => ['status' => 'pending', 'name' => 'create_task'],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'parent_user_message_id' => $parent->id,
            'role' => 'assistant',
            'content' => 'plain text only',
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'parent_user_message_id' => $otherParent->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => ['status' => 'pending', 'name' => 'create_project'],
        ]);

        $rows = (new ListPendingToolCallsForTurnAction)->execute($chat, $parent->id);

        $this->assertCount(1, $rows);
        $this->assertSame('create_task', $rows->first()->tool_call['name']);
    }
}
