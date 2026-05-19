<?php

namespace Tests\Unit\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Services\LlmInputBuilder;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LlmInputBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function newChat(): Chat
    {
        $user = User::factory()->create();

        return Chat::create(['user_id' => $user->id]);
    }

    private function user(Chat $chat, string $content, ?array $attachments = null): Message
    {
        return $chat->messages()->create([
            'role' => MessageRole::User->value,
            'content' => $content,
            'attachments' => $attachments,
        ]);
    }

    private function assistantText(Chat $chat, string $content): Message
    {
        return $chat->messages()->create([
            'role' => MessageRole::Assistant->value,
            'content' => $content,
        ]);
    }

    /**
     * @param  array<string, mixed>  $toolCall
     */
    private function assistantTool(Chat $chat, array $toolCall, string $content = ''): Message
    {
        return $chat->messages()->create([
            'role' => MessageRole::Assistant->value,
            'content' => $content,
            'tool_call' => $toolCall,
        ]);
    }

    private function defaultToolCall(string $status, string $callId = 'fc_1'): array
    {
        return [
            'tool_call_id' => $callId,
            'name' => 'create_task',
            'arguments' => ['project_id' => 1, 'name' => 'Task A'],
            'status' => $status,
        ];
    }

    public function test_single_user_message_emits_one_message_item_with_input_text(): void
    {
        $chat = $this->newChat();
        $this->user($chat, 'Hello');

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame([[
            'type' => 'message',
            'role' => 'user',
            'content' => [['type' => 'input_text', 'text' => 'Hello']],
        ]], $items);
    }

    public function test_user_message_with_image_attachment_emits_input_image_before_text(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('img.png', 'fake-png-bytes');

        $chat = $this->newChat();
        $this->user($chat, 'What is this?', [[
            'type' => 'image',
            'path' => 'img.png',
            'url' => 'http://example.test/img.png',
            'mime' => 'image/png',
        ]]);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $expectedDataUrl = 'data:image/png;base64,'.base64_encode('fake-png-bytes');
        $this->assertCount(1, $items);
        $this->assertSame('message', $items[0]['type']);
        $this->assertSame('user', $items[0]['role']);
        $this->assertSame('input_image', $items[0]['content'][0]['type']);
        $this->assertSame($expectedDataUrl, $items[0]['content'][0]['image_url']);
        $this->assertSame('input_text', $items[0]['content'][1]['type']);
        $this->assertSame('What is this?', $items[0]['content'][1]['text']);
    }

    public function test_user_message_with_audio_transcript_folds_into_text(): void
    {
        $chat = $this->newChat();
        $this->user($chat, 'Heads up:', [[
            'type' => 'audio',
            'path' => 'audio.webm',
            'url' => 'http://example.test/audio.webm',
            'transcript' => 'meeting at 3',
        ]]);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(1, $items);
        $this->assertSame('input_text', $items[0]['content'][0]['type']);
        $this->assertSame("Heads up:\n\n[Voice note] meeting at 3", $items[0]['content'][0]['text']);
    }

    public function test_function_call_arguments_hide_server_only_image_attachments_from_llm(): void
    {
        // Regression: the tools (CreateTaskTool, CreateProjectTool) snapshot
        // user-message attachments into tool_call.arguments under
        // `image_attachments` / `logo_attachment` so the confirm path and the
        // card renderer can read them later. The LLM must NOT see those keys
        // in its function_call history — otherwise it copies them onto the
        // next create_task and the new task ends up "inheriting" an image from
        // an unrelated previous message. We strip the keys when serialising
        // tool calls for the LLM.
        $chat = $this->newChat();
        $this->assistantTool($chat, [
            'tool_call_id' => 'fc_img',
            'name' => 'create_task',
            'arguments' => [
                'project_id' => 1,
                'name' => 'Logo hinzufügen',
                'image_attachments' => [[
                    'path' => 'leaked.png',
                    'url' => 'http://example.test/leaked.png',
                    'mime' => 'image/png',
                    'name' => 'leaked.png',
                ]],
            ],
            'status' => 'created',
            'result' => ['task_id' => 7],
        ]);
        $this->assistantTool($chat, [
            'tool_call_id' => 'fc_logo',
            'name' => 'create_project',
            'arguments' => [
                'team_id' => 1,
                'name' => 'Marketing',
                'logo_attachment' => [
                    'path' => 'logo.png',
                    'url' => 'http://example.test/logo.png',
                ],
            ],
            'status' => 'created',
            'result' => ['project_id' => 9],
        ]);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $createTaskCall = collect($items)->first(
            fn ($item) => ($item['type'] ?? null) === 'function_call' && ($item['call_id'] ?? null) === 'fc_img',
        );
        $createProjectCall = collect($items)->first(
            fn ($item) => ($item['type'] ?? null) === 'function_call' && ($item['call_id'] ?? null) === 'fc_logo',
        );

        $this->assertNotNull($createTaskCall);
        $this->assertNotNull($createProjectCall);

        $this->assertStringNotContainsString('image_attachments', $createTaskCall['arguments']);
        $this->assertStringNotContainsString('leaked.png', $createTaskCall['arguments']);
        $this->assertStringContainsString('"name":"Logo hinzu', $createTaskCall['arguments']);
        $this->assertStringContainsString('"project_id":1', $createTaskCall['arguments']);

        $this->assertStringNotContainsString('logo_attachment', $createProjectCall['arguments']);
        $this->assertStringNotContainsString('logo.png', $createProjectCall['arguments']);
        $this->assertStringContainsString('"name":"Marketing"', $createProjectCall['arguments']);
    }

    public function test_pending_tool_call_emits_function_call_and_awaiting_output(): void
    {
        $chat = $this->newChat();
        $this->assistantTool($chat, $this->defaultToolCall('pending'));

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(2, $items);
        $this->assertSame('function_call', $items[0]['type']);
        $this->assertSame('fc_1', $items[0]['call_id']);
        $this->assertSame('create_task', $items[0]['name']);
        $this->assertSame('{"project_id":1,"name":"Task A"}', $items[0]['arguments']);

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertSame('fc_1', $items[1]['call_id']);
        $this->assertSame('Awaiting user confirmation in the UI.', $items[1]['output']);
    }

    public function test_created_tool_call_output_mentions_task_id_when_present(): void
    {
        $chat = $this->newChat();
        $toolCall = $this->defaultToolCall('created');
        $toolCall['result'] = ['task_id' => 42];
        $this->assistantTool($chat, $toolCall);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertStringContainsString('task_id=42', $items[1]['output']);
        $this->assertStringContainsString('Confirmed by the user', $items[1]['output']);
    }

    public function test_created_tool_call_output_mentions_project_id_for_create_project(): void
    {
        $chat = $this->newChat();
        $this->assistantTool($chat, [
            'tool_call_id' => 'fc_p',
            'name' => 'create_project',
            'arguments' => ['team_id' => 1, 'name' => 'Site'],
            'status' => 'created',
            'result' => ['project_id' => 7, 'team_id' => 1],
        ]);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertStringContainsString('Project created', $items[1]['output']);
        $this->assertStringContainsString('project_id=7', $items[1]['output']);
        $this->assertStringNotContainsString('Task created', $items[1]['output']);
    }

    public function test_answered_ask_user_choice_output_includes_chosen_label(): void
    {
        $chat = $this->newChat();
        $this->assistantTool($chat, [
            'tool_call_id' => 'fc_ch',
            'name' => 'ask_user_choice',
            'arguments' => [
                'question' => 'Which team?',
                'options' => ['Folienzuschnitt', "Raul's Team"],
            ],
            'status' => 'answered',
            'result' => ['choice_index' => 0, 'choice_label' => 'Folienzuschnitt'],
        ]);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertStringContainsString('User chose: Folienzuschnitt', $items[1]['output']);
    }

    public function test_discarded_tool_call_output_warns_not_to_retry(): void
    {
        $chat = $this->newChat();
        $this->assistantTool($chat, $this->defaultToolCall('discarded'));

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertStringContainsString('discarded', $items[1]['output']);
        $this->assertStringContainsString('Do not retry', $items[1]['output']);
    }

    public function test_failed_tool_call_output_contains_validation_errors(): void
    {
        $chat = $this->newChat();
        $toolCall = $this->defaultToolCall('failed');
        $toolCall['validation_errors'] = ['project_id is invalid', 'name is required'];
        $this->assistantTool($chat, $toolCall);

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertSame('function_call_output', $items[1]['type']);
        $this->assertStringContainsString('project_id is invalid', $items[1]['output']);
        $this->assertStringContainsString('name is required', $items[1]['output']);
        $this->assertStringContainsString('Do not retry', $items[1]['output']);
    }

    public function test_assistant_row_with_text_and_tool_call_emits_message_and_function_call_pair(): void
    {
        $chat = $this->newChat();
        $this->assistantTool($chat, $this->defaultToolCall('pending'), 'Creating the task now…');

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(3, $items);
        $this->assertSame('message', $items[0]['type']);
        $this->assertSame('assistant', $items[0]['role']);
        $this->assertSame('output_text', $items[0]['content'][0]['type']);
        $this->assertSame('Creating the task now…', $items[0]['content'][0]['text']);
        $this->assertSame('function_call', $items[1]['type']);
        $this->assertSame('function_call_output', $items[2]['type']);
    }

    public function test_missing_call_id_skips_function_call_pair_safely(): void
    {
        $chat = $this->newChat();
        $broken = $this->defaultToolCall('pending');
        unset($broken['tool_call_id']);
        $this->assistantTool($chat, $broken, 'partial text');

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(1, $items);
        $this->assertSame('message', $items[0]['type']);
        $this->assertSame('partial text', $items[0]['content'][0]['text']);
    }

    public function test_user_then_pending_tool_call_then_user_emits_items_in_order(): void
    {
        $chat = $this->newChat();
        $this->user($chat, 'create task A');
        $this->assistantTool($chat, $this->defaultToolCall('pending', 'fc_a'));
        $this->user($chat, 'now create task B');

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(4, $items);
        $this->assertSame('message', $items[0]['type']);
        $this->assertSame('user', $items[0]['role']);
        $this->assertSame('create task A', $items[0]['content'][0]['text']);

        $this->assertSame('function_call', $items[1]['type']);
        $this->assertSame('fc_a', $items[1]['call_id']);

        $this->assertSame('function_call_output', $items[2]['type']);
        $this->assertSame('fc_a', $items[2]['call_id']);
        $this->assertSame('Awaiting user confirmation in the UI.', $items[2]['output']);

        $this->assertSame('message', $items[3]['type']);
        $this->assertSame('user', $items[3]['role']);
        $this->assertSame('now create task B', $items[3]['content'][0]['text']);
    }

    public function test_multiple_tool_calls_in_sequence_each_paired_with_own_output(): void
    {
        $chat = $this->newChat();
        $this->user($chat, 'first');
        $this->assistantTool($chat, $this->defaultToolCall('created', 'fc_1'));
        $this->user($chat, 'second');
        $this->assistantTool($chat, $this->defaultToolCall('pending', 'fc_2'));

        $items = app(LlmInputBuilder::class)->build($chat->messages()->get());

        $this->assertCount(6, $items);
        $this->assertSame('message', $items[0]['type']);
        $this->assertSame('function_call', $items[1]['type']);
        $this->assertSame('fc_1', $items[1]['call_id']);
        $this->assertSame('function_call_output', $items[2]['type']);
        $this->assertSame('fc_1', $items[2]['call_id']);
        $this->assertSame('message', $items[3]['type']);
        $this->assertSame('function_call', $items[4]['type']);
        $this->assertSame('fc_2', $items[4]['call_id']);
        $this->assertSame('function_call_output', $items[5]['type']);
        $this->assertSame('fc_2', $items[5]['call_id']);
    }
}
