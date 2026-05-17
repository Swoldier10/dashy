<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\CreateChatAction;
use App\Domains\Chat\Actions\CreateMessageAction;
use App\Domains\Chat\Actions\DeleteChatAction;
use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Actions\ListUserChatsAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_chat_persists_row(): void
    {
        $user = User::factory()->create();
        $chat = (new CreateChatAction)->execute(['user_id' => $user->id, 'title' => 'Hello']);
        $this->assertSame('Hello', $chat->title);
    }

    public function test_create_message_persists_row(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'X']);

        $message = (new CreateMessageAction)->execute([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'Hi',
        ]);

        $this->assertSame('Hi', $message->content);
    }

    public function test_delete_chat_cascades(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'a']);

        (new DeleteChatAction)->execute($chat);

        $this->assertSame(0, Message::count());
        $this->assertSame(0, Chat::count());
    }

    public function test_list_returns_user_chats_ordered_by_recency(): void
    {
        $user = User::factory()->create();
        $a = Chat::create(['user_id' => $user->id, 'title' => 'older']);
        $this->travel(5)->seconds();
        $b = Chat::create(['user_id' => $user->id, 'title' => 'newer']);

        $list = (new ListUserChatsAction)->execute($user);

        $this->assertSame($b->id, $list->first()->id);
    }

    public function test_list_excludes_other_users_chats(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Chat::create(['user_id' => $other->id, 'title' => 'theirs']);

        $this->assertCount(0, (new ListUserChatsAction)->execute($user));
    }

    public function test_find_returns_owned_chat_or_null(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'mine']);

        $this->assertNotNull((new FindChatForUserAction)->execute($user, $chat->id));
        $this->assertNull((new FindChatForUserAction)->execute($other, $chat->id));
        $this->assertNull((new FindChatForUserAction)->execute($user, 9999));
    }
}
