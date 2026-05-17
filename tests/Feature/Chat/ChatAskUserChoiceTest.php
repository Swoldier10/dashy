<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ChatAskUserChoiceTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCodex(): User
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);

        return $user;
    }

    public function test_clicking_an_option_records_choice_posts_user_message_and_resumes_assistant(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'create a new project',
        ]);
        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_choice',
                'name' => 'ask_user_choice',
                'arguments' => [
                    'question' => 'Which team should I create the project in?',
                    'options' => ['Folienzuschnitt', "Raul's Team"],
                ],
                'status' => 'pending',
            ],
        ]);

        // After the user picks, the assistant stream is resumed. Capture the
        // input items the LLM was handed so we can assert the choice landed
        // in history both as a function_call_output and as a user message.
        $captured = null;
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')
            ->once()
            ->andReturnUsing(function ($connection, $inputItems) use (&$captured) {
                $captured = $inputItems;
                yield from [];
            });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('answerChoice', $assistant->id, 0)
            ->call('processAssistantReply');

        // Choice persisted on the assistant message.
        $assistant->refresh();
        $this->assertSame('answered', $assistant->tool_call['status']);
        $this->assertSame(0, $assistant->tool_call['result']['choice_index']);
        $this->assertSame('Folienzuschnitt', $assistant->tool_call['result']['choice_label']);

        // A new user message with the chosen label was inserted.
        $latestUser = Message::query()
            ->where('chat_id', $chat->id)
            ->where('role', 'user')
            ->latest('id')
            ->first();
        $this->assertNotNull($latestUser);
        $this->assertSame('Folienzuschnitt', $latestUser->content);

        // LlmInputBuilder included the function_call_output AND the new user message.
        $this->assertIsArray($captured);
        $outputs = array_filter($captured, fn ($item) => ($item['type'] ?? null) === 'function_call_output');
        $this->assertNotEmpty($outputs);
        $lastOutput = end($outputs);
        $this->assertStringContainsString('User chose: Folienzuschnitt', $lastOutput['output']);

        $userItems = array_filter($captured, fn ($item) => ($item['type'] ?? null) === 'message' && ($item['role'] ?? null) === 'user');
        $userTexts = array_map(fn ($item) => $item['content'][0]['text'] ?? '', $userItems);
        $this->assertContains('Folienzuschnitt', $userTexts);
    }

    public function test_clicking_an_option_on_a_non_pending_call_is_rejected(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_choice',
                'name' => 'ask_user_choice',
                'arguments' => [
                    'question' => 'Which?',
                    'options' => ['A', 'B'],
                ],
                'status' => 'answered',
                'result' => ['choice_index' => 0, 'choice_label' => 'A'],
            ],
        ]);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('answerChoice', $assistant->id, 1);

        $assistant->refresh();
        // Still the prior selection — service rejected the second click.
        $this->assertSame(0, $assistant->tool_call['result']['choice_index']);

        $userMessages = Message::query()
            ->where('chat_id', $chat->id)
            ->where('role', 'user')
            ->count();
        $this->assertSame(0, $userMessages);
    }
}
