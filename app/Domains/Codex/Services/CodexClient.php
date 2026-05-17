<?php

namespace App\Domains\Codex\Services;

use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexApiException;
use App\Domains\Codex\Models\CodexConnection;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OpenAI Codex chat client (Responses API).
 *
 * Hits chatgpt.com/backend-api/codex/responses (the same endpoint the Codex
 * CLI uses). The caller supplies pre-shaped Responses-API `input` items; this
 * client just streams the SSE response and yields stream events — both text
 * deltas and function-call deltas/completions when tools are provided.
 */
class CodexClient
{
    private const CHAT_URL = 'https://chatgpt.com/backend-api/codex/responses';

    private const ORIGINATOR = 'dashy';

    public const DEFAULT_INSTRUCTIONS = 'You are a helpful AI assistant. Answer the user clearly and concisely.';

    public function __construct(
        private CodexAuthService $auth,
    ) {}

    /**
     * Stream chat-completion deltas. Yields ChatStreamEvent value objects.
     *
     * @param  array<int, array<string, mixed>>  $inputItems  Pre-shaped Responses-API input items (message/function_call/function_call_output)
     * @param  array<int, array<string, mixed>>  $tools
     * @return Generator<int, ChatStreamEvent>
     */
    public function streamChat(
        CodexConnection $connection,
        array $inputItems,
        ?string $instructions = null,
        array $tools = [],
        string $toolChoice = 'auto',
    ): Generator {
        $connection = $this->auth->ensureFreshToken($connection);

        $sessionId = (string) Str::uuid();

        $response = Http::withToken($connection->access_token)
            ->withHeaders([
                'originator' => self::ORIGINATOR,
                'User-Agent' => self::ORIGINATOR,
                'Accept' => 'text/event-stream',
                'OpenAI-Beta' => 'responses=experimental',
                'session_id' => $sessionId,
            ])
            ->withOptions(['stream' => true])
            ->post(self::CHAT_URL, [
                'model' => config('services.codex.model'),
                'instructions' => $instructions ?? self::DEFAULT_INSTRUCTIONS,
                'input' => $inputItems,
                'tools' => $tools,
                'tool_choice' => $toolChoice,
                'parallel_tool_calls' => true,
                'reasoning' => null,
                'store' => false,
                'stream' => true,
                'include' => [],
            ]);

        if ($response->failed()) {
            $bodyText = (string) $response->body();
            Log::warning('Codex API error', [
                'status' => $response->status(),
                'body' => Str::limit($bodyText, 2000),
            ]);

            throw new CodexApiException(sprintf(
                'Codex API error %d: %s',
                $response->status(),
                Str::limit($bodyText, 200) ?: '(empty body)',
            ));
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        /** @var array<int, array{call_id: string, name: string, arguments: string}> $toolCalls */
        $toolCalls = [];

        while (! $body->eof()) {
            $buffer .= $body->read(4096);

            while (($newline = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newline);
                $buffer = substr($buffer, $newline + 1);

                if (! str_starts_with($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, 5));
                if ($data === '' || $data === '[DONE]') {
                    continue;
                }

                $payload = json_decode($data, true);
                if (! is_array($payload)) {
                    continue;
                }

                yield from $this->translateEvent($payload, $toolCalls);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, array{call_id: string, name: string, arguments: string}>  $toolCalls
     * @return Generator<int, ChatStreamEvent>
     */
    private function translateEvent(array $payload, array &$toolCalls): Generator
    {
        $type = $payload['type'] ?? null;

        if ($type === 'response.output_text.delta') {
            $delta = $payload['delta'] ?? null;
            if (is_string($delta) && $delta !== '') {
                yield ChatStreamEvent::textDelta($delta);
            }

            return;
        }

        if ($type === 'response.output_item.added') {
            $item = $payload['item'] ?? null;
            if (is_array($item) && ($item['type'] ?? null) === 'function_call') {
                $index = (int) ($payload['output_index'] ?? 0);
                $callId = (string) ($item['call_id'] ?? $item['id'] ?? '');
                $name = (string) ($item['name'] ?? '');
                $toolCalls[$index] = [
                    'call_id' => $callId,
                    'name' => $name,
                    'arguments' => '',
                ];
                if ($callId !== '' && $name !== '') {
                    yield ChatStreamEvent::toolCallStarted($callId, $name);
                }
            }

            return;
        }

        if ($type === 'response.function_call_arguments.delta') {
            $index = (int) ($payload['output_index'] ?? 0);
            $delta = $payload['delta'] ?? '';
            if (! is_string($delta) || $delta === '' || ! isset($toolCalls[$index])) {
                return;
            }
            $toolCalls[$index]['arguments'] .= $delta;
            yield ChatStreamEvent::toolCallArgumentsDelta($toolCalls[$index]['call_id'], $delta);

            return;
        }

        if ($type === 'response.function_call_arguments.done' || $type === 'response.output_item.done') {
            $index = (int) ($payload['output_index'] ?? 0);
            if (! isset($toolCalls[$index])) {
                return;
            }

            $argumentsJson = $toolCalls[$index]['arguments'];
            if ($type === 'response.function_call_arguments.done' && isset($payload['arguments']) && is_string($payload['arguments'])) {
                $argumentsJson = $payload['arguments'];
            } elseif ($type === 'response.output_item.done') {
                $item = $payload['item'] ?? null;
                if (! is_array($item) || ($item['type'] ?? null) !== 'function_call') {
                    return;
                }
                if (isset($item['arguments']) && is_string($item['arguments'])) {
                    $argumentsJson = $item['arguments'];
                }
            }

            yield ChatStreamEvent::toolCallCompleted(
                $toolCalls[$index]['call_id'],
                $toolCalls[$index]['name'],
                $argumentsJson,
            );

            unset($toolCalls[$index]);
        }
    }

}
