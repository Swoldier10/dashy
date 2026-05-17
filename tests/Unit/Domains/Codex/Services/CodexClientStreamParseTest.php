<?php

namespace Tests\Unit\Domains\Codex\Services;

use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CodexClientStreamParseTest extends TestCase
{
    use RefreshDatabase;

    private function fakeStream(string $sse): void
    {
        Http::fake(function () use ($sse) {
            return Http::response(
                Utils::streamFor($sse),
                200,
                ['Content-Type' => 'text/event-stream'],
            );
        });
    }

    private function freshConnection(): CodexConnection
    {
        $user = User::factory()->create();

        return CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'token',
            'expires_at' => now()->addHour(),
        ]);
    }

    public function test_parses_text_only_stream_into_text_deltas(): void
    {
        $sse = implode("\n", [
            'data: {"type":"response.output_text.delta","delta":"Hel"}',
            'data: {"type":"response.output_text.delta","delta":"lo"}',
            'data: [DONE]',
            '',
        ]);
        $this->fakeStream($sse);

        $events = iterator_to_array(
            app(CodexClient::class)->streamChat(
                $this->freshConnection(),
                [['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'hi']]]],
            ),
            preserve_keys: false,
        );

        $this->assertCount(2, $events);
        $this->assertSame(ChatStreamEvent::TYPE_TEXT_DELTA, $events[0]->type);
        $this->assertSame('Hel', $events[0]->text);
        $this->assertSame('lo', $events[1]->text);
    }

    public function test_parses_function_call_into_tool_call_events(): void
    {
        $args = json_encode(['project_id' => 1, 'name' => 'X']);
        $sse = implode("\n", [
            'data: {"type":"response.output_item.added","output_index":0,"item":{"type":"function_call","call_id":"fc_42","id":"fc_42","name":"create_task"}}',
            'data: {"type":"response.function_call_arguments.delta","output_index":0,"delta":'.json_encode(substr($args, 0, 5)).'}',
            'data: {"type":"response.function_call_arguments.delta","output_index":0,"delta":'.json_encode(substr($args, 5)).'}',
            'data: {"type":"response.function_call_arguments.done","output_index":0,"arguments":'.json_encode($args).'}',
            'data: [DONE]',
            '',
        ]);
        $this->fakeStream($sse);

        $events = iterator_to_array(
            app(CodexClient::class)->streamChat(
                $this->freshConnection(),
                [['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'hi']]]],
                instructions: 'sys',
                tools: [['type' => 'function', 'name' => 'create_task', 'parameters' => ['type' => 'object']]],
            ),
            preserve_keys: false,
        );

        $types = array_map(fn ($e) => $e->type, $events);
        $this->assertContains(ChatStreamEvent::TYPE_TOOL_CALL_STARTED, $types);
        $this->assertContains(ChatStreamEvent::TYPE_TOOL_CALL_ARGUMENTS_DELTA, $types);
        $this->assertContains(ChatStreamEvent::TYPE_TOOL_CALL_COMPLETED, $types);

        $completed = collect($events)
            ->first(fn ($e) => $e->type === ChatStreamEvent::TYPE_TOOL_CALL_COMPLETED);
        $this->assertSame('fc_42', $completed->callId);
        $this->assertSame('create_task', $completed->name);
        $this->assertSame($args, $completed->argumentsJson);
    }
}
