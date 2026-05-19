<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\FindMessageForUserAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindMessageForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_message_when_user_owns_parent_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'hi']);

        $found = (new FindMessageForUserAction)->execute($user, $message->id);

        $this->assertNotNull($found);
        $this->assertSame($message->id, $found->id);
    }

    public function test_returns_null_when_message_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $chat = Chat::create(['user_id' => $owner->id]);
        $message = Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'hi']);

        $this->assertNull((new FindMessageForUserAction)->execute($intruder, $message->id));
    }

    public function test_returns_null_when_message_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->assertNull((new FindMessageForUserAction)->execute($user, 9999));
    }
}
