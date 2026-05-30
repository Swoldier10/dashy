<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\DiscardToolCallService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DiscardToolCallServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): DiscardToolCallService
    {
        return app(DiscardToolCallService::class);
    }

    private function assistantMessage(User $user, ?array $toolCall): Message
    {
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'c']);

        return Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => $toolCall,
        ]);
    }

    public function test_marks_a_pending_tool_call_as_discarded(): void
    {
        $user = User::factory()->create();
        $message = $this->assistantMessage($user, ['name' => 'create_task', 'status' => 'pending']);

        $result = $this->service()->execute($user, $message->id);

        $this->assertSame('discarded', $result['status']);
        $this->assertSame('discarded', $message->refresh()->tool_call['status']);
    }

    public function test_throws_when_the_message_is_not_found_for_the_actor(): void
    {
        $owner = User::factory()->create();
        $message = $this->assistantMessage($owner, ['status' => 'pending']);

        $this->expectException(ModelNotFoundException::class);
        $this->service()->execute(User::factory()->create(), $message->id);
    }

    public function test_rejects_a_non_assistant_message(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'c']);
        $userMessage = Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'hi']);

        $this->expectException(AuthorizationException::class);
        $this->service()->execute($user, $userMessage->id);
    }

    public function test_rejects_a_tool_call_that_is_not_pending(): void
    {
        $user = User::factory()->create();
        $message = $this->assistantMessage($user, ['status' => 'confirmed']);

        $this->expectException(RuntimeException::class);
        $this->service()->execute($user, $message->id);
    }
}
