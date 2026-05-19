<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\UpdateMessageToolCallAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMessageToolCallActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_tool_call_payload(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => ['status' => 'pending', 'name' => 'create_task'],
        ]);

        (new UpdateMessageToolCallAction)->execute($message, [
            'status' => 'created',
            'name' => 'create_task',
            'result' => ['task_id' => 42],
        ]);

        $message->refresh();
        $this->assertSame('created', $message->tool_call['status']);
        $this->assertSame(42, $message->tool_call['result']['task_id']);
    }
}
