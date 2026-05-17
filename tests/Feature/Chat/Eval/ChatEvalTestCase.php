<?php

namespace Tests\Feature\Chat\Eval;

use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Services\CodexClient;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Base class for the chat-eval harness. Each subclass is one frozen
 * "user said X → expected tool calls Y" scenario; the LLM is mocked with a
 * deterministic transcript so the test pins agent behaviour without burning
 * real API tokens. Running `php artisan test --filter Chat\\Eval` re-runs
 * the whole suite — a regression in the prompt or runtime breaks here
 * before it ships.
 */
abstract class ChatEvalTestCase extends TestCase
{
    /**
     * Mock the CodexClient with a single canned stream. Use this in every
     * eval case so the model output is bit-for-bit deterministic.
     *
     * @param  list<ChatStreamEvent>  $events
     */
    protected function fakeCodexStream(array $events): void
    {
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($events) {
            foreach ($events as $event) {
                yield $event;
            }
        });
        $this->app->instance(CodexClient::class, $mock);
    }

    /**
     * Convenience builder for a single tool-call stream (started → completed
     * with JSON arguments).
     *
     * @return list<ChatStreamEvent>
     */
    protected function toolCallStream(string $callId, string $toolName, array $arguments, string $text = ''): array
    {
        $events = [];
        if ($text !== '') {
            $events[] = ChatStreamEvent::textDelta($text);
        }
        $events[] = ChatStreamEvent::toolCallStarted($callId, $toolName);
        $events[] = ChatStreamEvent::toolCallCompleted(
            $callId,
            $toolName,
            (string) json_encode($arguments),
        );

        return $events;
    }

    /**
     * Drive the chat panel through one user-message → assistant-reply cycle.
     */
    protected function runOneTurn(string $userMessage): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test('chat.chat-panel')
            ->set('message', $userMessage)
            ->call('sendMessage')
            ->call('processAssistantReply');
    }
}
