<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\AnswerChoiceService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AnswerChoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function pendingChoiceMessage(User $user, array $options = ['Alpha', 'Beta']): Message
    {
        $chat = Chat::create(['user_id' => $user->id]);

        return Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_choice',
                'name' => 'ask_user_choice',
                'arguments' => [
                    'question' => 'Which one?',
                    'options' => $options,
                ],
                'status' => 'pending',
            ],
        ]);
    }

    public function test_records_chosen_option_and_returns_label(): void
    {
        $user = User::factory()->create();
        $message = $this->pendingChoiceMessage($user, ['Alpha', 'Beta', 'Gamma']);

        $label = app(AnswerChoiceService::class)->execute($user, $message->id, 1);

        $this->assertSame('Beta', $label);

        $message->refresh();
        $this->assertSame('answered', $message->tool_call['status']);
        $this->assertSame(1, $message->tool_call['result']['choice_index']);
        $this->assertSame('Beta', $message->tool_call['result']['choice_label']);
    }

    public function test_rejects_unknown_message(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(AnswerChoiceService::class)->execute($user, 99999, 0);
    }

    public function test_rejects_message_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $message = $this->pendingChoiceMessage($owner);

        $this->expectException(ModelNotFoundException::class);

        app(AnswerChoiceService::class)->execute($intruder, $message->id, 0);
    }

    public function test_rejects_user_role_message(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'hello',
        ]);

        $this->expectException(AuthorizationException::class);

        app(AnswerChoiceService::class)->execute($user, $message->id, 0);
    }

    public function test_rejects_different_tool_name(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_task',
                'name' => 'create_task',
                'arguments' => ['project_id' => 1, 'name' => 'X'],
                'status' => 'pending',
            ],
        ]);

        $this->expectException(RuntimeException::class);

        app(AnswerChoiceService::class)->execute($user, $message->id, 0);
    }

    public function test_rejects_non_pending_status(): void
    {
        $user = User::factory()->create();
        $message = $this->pendingChoiceMessage($user);
        $message->tool_call = array_merge($message->tool_call, ['status' => 'answered']);
        $message->save();

        $this->expectException(RuntimeException::class);

        app(AnswerChoiceService::class)->execute($user, $message->id, 0);
    }

    public function test_rejects_out_of_bounds_index(): void
    {
        $user = User::factory()->create();
        $message = $this->pendingChoiceMessage($user, ['A', 'B']);

        $this->expectException(RuntimeException::class);

        app(AnswerChoiceService::class)->execute($user, $message->id, 5);
    }

    public function test_rejects_negative_index(): void
    {
        $user = User::factory()->create();
        $message = $this->pendingChoiceMessage($user, ['A', 'B']);

        $this->expectException(RuntimeException::class);

        app(AnswerChoiceService::class)->execute($user, $message->id, -1);
    }
}
