<?php

namespace Tests\Unit\Domains\Codex\Services;

use App\Domains\Codex\Exceptions\CodexApiException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CodexClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a Responses-API SSE body containing the given text deltas plus
     * a few framing events the client should ignore.
     */
    private function buildResponsesSse(array $deltas): string
    {
        $body = 'data: '.json_encode(['type' => 'response.created', 'response' => ['id' => 'resp_1']])."\n";
        foreach ($deltas as $delta) {
            $body .= 'data: '.json_encode(['type' => 'response.output_text.delta', 'delta' => $delta])."\n";
        }
        $body .= 'data: '.json_encode(['type' => 'response.completed'])."\n";
        $body .= "data: [DONE]\n";

        return $body;
    }

    private function connection(): CodexConnection
    {
        $user = User::factory()->create();

        return CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'token',
            'expires_at' => now()->addHour(),
        ]);
    }

    public function test_yields_only_output_text_deltas_in_order(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response(
                $this->buildResponsesSse(['Hello', ' ', 'world']),
                200,
            ),
        ]);

        $deltas = [];
        foreach (app(CodexClient::class)->streamChat($this->connection(), [
            ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
        ]) as $event) {
            $deltas[] = $event->text;
        }

        $this->assertSame(['Hello', ' ', 'world'], $deltas);
    }

    public function test_ignores_unknown_event_types(): void
    {
        $body = 'data: '.json_encode(['type' => 'response.reasoning_text.delta', 'delta' => 'thinking…'])."\n";
        $body .= 'data: '.json_encode(['type' => 'response.output_text.delta', 'delta' => 'A'])."\n";
        $body .= "data: [DONE]\n";

        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response($body, 200),
        ]);

        $deltas = [];
        foreach (app(CodexClient::class)->streamChat($this->connection(), [
            ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
        ]) as $event) {
            $deltas[] = $event->text;
        }

        $this->assertSame(['A'], $deltas);
    }

    public function test_throws_when_response_failed(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response('boom', 500),
        ]);

        $this->expectException(CodexApiException::class);

        foreach (app(CodexClient::class)->streamChat($this->connection(), [
            ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
        ]) as $_) {
            // consume
        }
    }

    public function test_forwards_pre_shaped_input_items_verbatim(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response(
                $this->buildResponsesSse(['ok']),
                200,
            ),
        ]);

        $items = [
            [
                'type' => 'message',
                'role' => 'user',
                'content' => [['type' => 'input_text', 'text' => 'Hello']],
            ],
            [
                'type' => 'function_call',
                'call_id' => 'fc_1',
                'name' => 'create_task',
                'arguments' => '{"name":"X"}',
            ],
            [
                'type' => 'function_call_output',
                'call_id' => 'fc_1',
                'output' => 'Awaiting user confirmation in the UI.',
            ],
            [
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'output_text', 'text' => 'Hi']],
            ],
        ];

        foreach (app(CodexClient::class)->streamChat($this->connection(), $items) as $_) {
            // consume
        }

        Http::assertSent(function ($request) use ($items) {
            $body = $request->data();

            return $body['stream'] === true
                && $body['tool_choice'] === 'auto'
                && $body['parallel_tool_calls'] === true
                && $body['tools'] === []
                && $body['include'] === []
                && $body['store'] === false
                && is_string($body['instructions'])
                && $body['input'] === $items;
        });
    }

    public function test_failure_includes_status_and_body_in_exception(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response('{"error":{"message":"missing field tools"}}', 400),
        ]);

        try {
            foreach (app(CodexClient::class)->streamChat($this->connection(), [
                ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
            ]) as $_) {
                // consume
            }
            $this->fail('Expected CodexApiException.');
        } catch (CodexApiException $e) {
            $this->assertStringContainsString('400', $e->getMessage());
            $this->assertStringContainsString('missing field', $e->getMessage());
        }
    }

    public function test_insufficient_quota_429_is_flagged_as_out_of_credits(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response(
                '{"error":{"type":"insufficient_quota","message":"You exceeded your current quota"}}',
                429,
            ),
        ]);

        try {
            foreach (app(CodexClient::class)->streamChat($this->connection(), [
                ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
            ]) as $_) {
                // consume
            }
            $this->fail('Expected CodexApiException.');
        } catch (CodexApiException $e) {
            $this->assertTrue($e->isOutOfCredits());
            $this->assertSame(429, $e->status);
        }
    }

    public function test_401_drops_the_connection_and_throws_not_connected(): void
    {
        Http::fake([
            'https://chatgpt.com/backend-api/codex/responses' => Http::response('{"error":{"message":"unauthorized"}}', 401),
        ]);

        $connection = $this->connection();
        $this->assertSame(1, CodexConnection::count());

        try {
            foreach (app(CodexClient::class)->streamChat($connection, [
                ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
            ]) as $_) {
                // consume
            }
            $this->fail('Expected CodexNotConnectedException.');
        } catch (CodexNotConnectedException) {
            $this->assertSame(0, CodexConnection::count(), 'A 401 should drop the dead connection so the UI can prompt reconnect.');
        }
    }

    public function test_connection_failure_becomes_a_typed_connection_error(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        try {
            foreach (app(CodexClient::class)->streamChat($this->connection(), [
                ['type' => 'message', 'role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hi']]],
            ]) as $_) {
                // consume
            }
            $this->fail('Expected CodexApiException.');
        } catch (CodexApiException $e) {
            $this->assertTrue($e->isConnectionError);
            $this->assertNull($e->status);
        }
    }
}
