<?php

namespace Tests\Unit\Domains\Chat\Models;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTouchTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_message_bumps_chat_updated_at(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Test']);

        $original = $chat->updated_at;

        $this->travel(5)->seconds();

        Message::create([
            'chat_id' => $chat->id,
            'role' => MessageRole::User->value,
            'content' => 'hello',
        ]);

        $this->assertGreaterThan($original->timestamp, $chat->fresh()->updated_at->timestamp);
    }

    public function test_chat_delete_cascades_to_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'X']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'a']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'b']);

        $chat->delete();

        $this->assertSame(0, Message::count());
    }
}
