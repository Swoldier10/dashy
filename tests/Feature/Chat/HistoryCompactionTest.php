<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Ai\Services\LlmInputBuilder;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3: LlmInputBuilder must honour is_summary — it folds away every
 * message before the most-recent summary so the LLM prompt stays bounded
 * even on long chats.
 */
class HistoryCompactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_collapses_messages_before_it(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Long chat']);

        // 5 older messages — would normally contribute 5 input items.
        for ($i = 0; $i < 5; $i++) {
            Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => "Old message {$i}",
            ]);
        }

        // The summary message — everything older is folded.
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => 'Summary of the first 5 messages.',
            'is_summary' => true,
        ]);

        // 2 fresh messages after the summary — these survive.
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'Fresh question']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'Fresh answer']);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        // Expect: 1 summary + 2 tail = 3 message items.
        $this->assertCount(3, $items);

        $texts = array_map(
            fn (array $item) => $item['content'][0]['text'] ?? '',
            $items,
        );
        $this->assertStringContainsString('Summary of the first 5 messages.', $texts[0]);
        $this->assertSame('Fresh question', $texts[1]);
        $this->assertSame('Fresh answer', $texts[2]);
    }

    public function test_no_summary_returns_every_message(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'one']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'two']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'three']);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());
        $this->assertCount(3, $items);
    }
}
