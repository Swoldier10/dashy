<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\HasRecentChatSummaryAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasRecentChatSummaryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_true_when_summary_message_exists(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'recap', 'is_summary' => true]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'next']);

        $this->assertTrue((new HasRecentChatSummaryAction)->execute($chat, 10));
    }

    public function test_false_when_no_summary_exists(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'a']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'b']);

        $this->assertFalse((new HasRecentChatSummaryAction)->execute($chat, 10));
    }
}
