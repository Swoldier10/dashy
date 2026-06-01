<?php

namespace App\Livewire\Chat\Concerns;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\FindLatestUserMessageIdService;
use App\Domains\Chat\Services\ListMessagesForChatService;
use App\Domains\Chat\Services\SendMessageService;
use App\Domains\Chat\Services\UpdateChatStopStateService;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexApiException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Throwable;

trait HandlesAssistantStream
{
    public string $streamingAssistant = '';

    public bool $isThinking = false;

    /**
     * Counts how many times the assistant has been invoked for the current
     * user turn. Each pass through `processAssistantReply()` increments this;
     * the runtime stops automatically at MAX_TURN_ITERATIONS to bound
     * runaway loops. Reset to 0 whenever a fresh user message lands.
     */
    public int $turnIteration = 0;

    private const MAX_TURN_ITERATIONS = 6;

    #[Computed]
    public function threadMessages(): Collection
    {
        $chat = $this->activeChat;
        if ($chat === null) {
            return new Collection;
        }

        return app(ListMessagesForChatService::class)->execute($chat);
    }

    /**
     * The user wants the assistant to stop after the current LLM call
     * finishes. Persists `stop_requested_at` on the chat so the loop checks
     * it at the next iteration boundary and bails out. Cleared automatically
     * when the next user message lands.
     */
    public function requestStop(): void
    {
        $chat = $this->activeChat;
        if ($chat === null) {
            return;
        }

        app(UpdateChatStopStateService::class)->requestStop(Auth::user(), $chat);
        $this->toast('info', __('Stopping…'));
        unset($this->activeChat);
    }

    #[On('process-assistant-reply')]
    public function processAssistantReply(SendMessageService $sendMessage): void
    {
        if ($this->activeChatId === null) {
            $this->isThinking = false;

            return;
        }

        $chat = $this->activeChat;
        if ($chat === null) {
            $this->isThinking = false;

            return;
        }

        // Honour the Stop button: if a stop was requested between the last
        // iteration and this one, bail before talking to the LLM again. The
        // flag clears automatically when the next user message lands.
        if ($chat->stop_requested_at !== null) {
            $this->toast('info', __('Stopped.'));
            $this->isThinking = false;
            $this->streamingAssistant = '';

            return;
        }

        $this->turnIteration++;
        if ($this->turnIteration > self::MAX_TURN_ITERATIONS) {
            $this->toast(
                'warning',
                __('Stopped after :max steps. Send a new message to continue.', ['max' => self::MAX_TURN_ITERATIONS]),
            );
            $this->isThinking = false;
            $this->streamingAssistant = '';

            return;
        }

        $parentUserMessageId = $this->latestUserMessageId($chat);

        $assembled = '';
        /** @var array<int, ChatStreamEvent> $toolCallEvents */
        $toolCallEvents = [];
        $statuses = [];

        try {
            foreach ($sendMessage->streamAssistant($chat, Auth::user(), $this->screen) as $event) {
                if ($this->isThinking) {
                    $this->isThinking = false;
                }

                if ($event->type === ChatStreamEvent::TYPE_TEXT_DELTA) {
                    $assembled .= (string) $event->text;
                    $this->stream(to: 'streamingAssistant', content: (string) $event->text, replace: false);
                } elseif ($event->type === ChatStreamEvent::TYPE_TOOL_CALL_COMPLETED) {
                    $toolCallEvents[] = $event;
                }
            }
        } catch (CodexNotConnectedException) {
            // No connection, revoked token (401), or a failed refresh. The
            // connection has been removed, so the composer re-renders into the
            // "Connect Codex" state via the isCodexConnected computed.
            $this->savePartial($sendMessage, $chat, $assembled, $parentUserMessageId);
            $this->addError('message', __('Connect Codex before sending a message.'));
            $this->finishTurn();

            return;
        } catch (CodexApiException $e) {
            // Out of credits, rate limited, billing, server error, network
            // failure, truncated stream — surface the specific friendly message.
            $this->savePartial($sendMessage, $chat, $assembled, $parentUserMessageId);
            $this->toast('danger', $e->userMessage());
            report($e);
            $this->finishTurn();

            return;
        } catch (Throwable $e) {
            // Anything unforeseen (prompt-builder bug, DB hiccup mid-stream, …)
            // degrades to a toast — never a raw 500.
            $this->savePartial($sendMessage, $chat, $assembled, $parentUserMessageId);
            $this->toast('danger', __('Something went wrong. Please try again.'));
            report($e);
            $this->finishTurn();

            return;
        }

        // Stream completed cleanly. Persist the assistant turn — but guard the
        // persistence too: a DB/dispatch hiccup here must degrade to a toast,
        // not a 500. Text is saved first so DB order matches stream order
        // (text typically precedes tool calls within one model response).
        try {
            if ($assembled !== '') {
                $sendMessage->saveAssistantMessage($chat, $assembled, null, $parentUserMessageId);
            }

            foreach ($toolCallEvents as $event) {
                $payload = $sendMessage->dispatchToolCall(Auth::user(), $event, $chat);
                $sendMessage->saveAssistantMessage($chat, '', $payload, $parentUserMessageId);
                $statuses[] = (string) ($payload['status'] ?? '');
            }
        } catch (Throwable $e) {
            $this->toast('danger', __('Something went wrong. Please try again.'));
            report($e);
            $this->finishTurn();

            return;
        }

        $this->streamingAssistant = '';
        $this->isThinking = false;
        unset($this->activeChat);

        $this->maybeContinueLoop($statuses, $toolCallEvents !== [], $assembled);
    }

    /**
     * Persist whatever assistant text streamed before an error, tagged as
     * interrupted, so the user keeps the partial reply. Swallows its own
     * persistence failure (already in an error path — never escalate to a 500).
     */
    private function savePartial(
        SendMessageService $sendMessage,
        Chat $chat,
        string $assembled,
        ?int $parentUserMessageId,
    ): void {
        if ($assembled === '') {
            return;
        }

        try {
            $sendMessage->saveAssistantMessage(
                $chat,
                $assembled."\n\n_(stream interrupted)_",
                null,
                $parentUserMessageId,
            );
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Decide whether to immediately re-invoke the assistant (looping for
     * multi-step reasoning) or stop and wait for the user.
     *
     * Continues when only auto-read tools fired (status=executed) or when
     * the model wrote only text and there are no pending cards — actually
     * text-only ends the turn. Pauses on any pending card so the user can
     * confirm or discard.
     *
     * @param  list<string>  $toolStatuses
     */
    private function maybeContinueLoop(array $toolStatuses, bool $emittedTools, string $assembled): void
    {
        $hasPending = in_array('pending', $toolStatuses, true);
        $hasExecuted = in_array('executed', $toolStatuses, true);

        if ($hasPending) {
            return;
        }

        if (! $emittedTools) {
            return;
        }

        if ($hasExecuted) {
            $this->isThinking = true;
            $this->dispatch('process-assistant-reply');
        }
    }

    private function finishTurn(): void
    {
        $this->streamingAssistant = '';
        $this->isThinking = false;
        unset($this->activeChat);
    }

    /**
     * The id of the most recent user message in this chat — used as the
     * parent for every assistant/tool row this turn produces, so we can group
     * them later for replay, debugging, and per-turn compaction.
     */
    protected function latestUserMessageId(Chat $chat): ?int
    {
        return app(FindLatestUserMessageIdService::class)->execute($chat);
    }
}
