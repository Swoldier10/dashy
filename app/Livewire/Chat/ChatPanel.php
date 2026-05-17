<?php

namespace App\Livewire\Chat;

use App\Domains\Calendar\Services\ListTodayAgendaService;
use App\Domains\Calendar\Enums\AgendaKind;
use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Ai\Services\AiToolCardPresenter;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\AnswerChoiceService;
use App\Domains\Chat\Services\ChatAttachmentService;
use App\Domains\Chat\Services\ConfirmToolCallService;
use App\Domains\Chat\Services\CreateChatService;
use App\Domains\Chat\Services\DiscardToolCallService;
use App\Domains\Chat\Services\SendMessageService;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexApiException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Carbon\CarbonImmutable;
use Throwable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ChatPanel extends Component
{
    use DispatchesDashyUi;
    use WithFileUploads;

    public ?int $activeChatId = null;

    /**
     * Optional viewport hint when the chat is mounted from a specific page
     * (a task detail drawer, a project view, etc.). Forwarded to the AI
     * system prompt so the model can resolve "this task" without needing
     * the user to repeat the name. Shape: `{type, id, name?}`.
     *
     * @var array<string, mixed>|null
     */
    public ?array $screen = null;

    public string $message = '';

    public string $streamingAssistant = '';

    public bool $isThinking = false;

    /** @var array<int, TemporaryUploadedFile> */
    public array $imageUploads = [];

    public ?TemporaryUploadedFile $voiceUpload = null;

    /** @var array<int, array<string, mixed>> */
    public array $persistedAttachments = [];

    /**
     * In-flight edits for each pending tool_call message, keyed by message id.
     * Seeded from the LLM-proposed arguments on first render; live-bound to
     * the inputs in the card partial; passed to ConfirmToolCallService on
     * Create.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $toolCallEdits = [];

    /**
     * Temporary uploads for replacing a project logo from within the preview
     * card, keyed by message id.
     *
     * @var array<int, TemporaryUploadedFile|null>
     */
    public array $toolCallLogoUploads = [];

    /**
     * Counts how many times the assistant has been invoked for the current
     * user turn. Each pass through `processAssistantReply()` increments this;
     * the runtime stops automatically at MAX_TURN_ITERATIONS to bound
     * runaway loops. Reset to 0 whenever a fresh user message lands.
     */
    public int $turnIteration = 0;

    private const MAX_TURN_ITERATIONS = 6;

    /**
     * @param  array{type: string, id?: int, name?: string}|null  $screen
     */
    public function mount(?int $chat = null, ?array $screen = null): void
    {
        if ($screen !== null && isset($screen['type'])) {
            $this->screen = $screen;
        }

        if ($chat === null) {
            return;
        }

        $found = app(FindChatForUserAction::class)->execute(Auth::user(), $chat);
        if ($found !== null) {
            $this->activeChatId = $found->id;
        }
    }

    #[Computed]
    public function activeChat(): ?Chat
    {
        if ($this->activeChatId === null) {
            return null;
        }

        return app(FindChatForUserAction::class)->execute(Auth::user(), $this->activeChatId);
    }

    #[Computed]
    public function threadMessages(): Collection
    {
        $chat = $this->activeChat;
        if ($chat === null) {
            return new Collection;
        }

        return $chat->messages()->get();
    }

    #[Computed]
    public function isCodexConnected(): bool
    {
        return app(FindCodexConnectionForUserAction::class)->execute(Auth::user()) !== null;
    }

    #[Computed]
    public function modelLabel(): string
    {
        return (string) config('services.codex.model');
    }

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function availableProjects(): Collection
    {
        return app(ListProjectsForUserService::class)->execute(Auth::user());
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = (int) now()->format('G');
        $part = match (true) {
            $hour < 12 => __('Good morning'),
            $hour < 18 => __('Good afternoon'),
            default => __('Good evening'),
        };

        $user = Auth::user();
        $first = $user->first_name
            ?: (string) (explode(' ', (string) ($user->name ?: $user->email))[0] ?? '');

        return $first !== '' ? "{$part}, {$first}" : $part;
    }

    /**
     * @return array{date: string, time: string}
     */
    #[Computed]
    public function dateTimePill(): array
    {
        $now = CarbonImmutable::now();

        return [
            'date' => mb_strtoupper($now->format('l, F j')),
            'time' => $now->format('g:i A'),
        ];
    }

    /**
     * @return array{meetings: int, tasks: int}
     */
    #[Computed]
    public function tomorrowSummary(): array
    {
        $rows = app(ListTodayAgendaService::class)->executeFor(
            Auth::user(),
            CarbonImmutable::tomorrow(),
        );

        $meetings = 0;
        $tasks = 0;
        foreach ($rows as $row) {
            $row->kind === AgendaKind::Event ? $meetings++ : $tasks++;
        }

        return ['meetings' => $meetings, 'tasks' => $tasks];
    }

    #[On('codex-connected')]
    public function refreshCodexConnection(): void
    {
        // Empty body — listener triggers re-render so isCodexConnected re-evaluates.
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

        $chat->forceFill(['stop_requested_at' => now()])->save();
        $this->toast('info', __('Stopping…'));
        unset($this->activeChat);
    }

    public function sendMessage(SendMessageService $sendMessage, CreateChatService $createChat): void
    {
        $this->resetErrorBag('message');
        $this->resetValidation('message');

        $hasText = trim($this->message) !== '';
        $hasAttachments = $this->persistedAttachments !== [];

        if (! $hasText && ! $hasAttachments) {
            return;
        }

        $this->validate([
            'message' => ['nullable', 'string', 'max:8000'],
        ]);

        if (! $this->isCodexConnected) {
            $this->addError('message', __('Connect Codex before sending a message.'));

            return;
        }

        $isNewChat = $this->activeChat === null;

        if ($isNewChat) {
            // CreateChatService writes the chat + first message in one
            // transaction. When attachments are present we pass them through
            // so the very first user message includes them.
            $seed = $hasText ? $this->message : $this->seedFromAttachments();
            $chat = $createChat->execute(Auth::user(), $seed, $this->persistedAttachments);
        } else {
            $chat = $this->activeChat;
            $sendMessage->saveUserMessage($chat, $this->message, $this->persistedAttachments);
        }

        // A new user message ends any prior stop request — the loop is fresh.
        if ($chat->stop_requested_at !== null) {
            $chat->forceFill(['stop_requested_at' => null])->save();
        }

        $this->activeChatId = $chat->id;
        $this->message = '';
        $this->persistedAttachments = [];
        $this->voiceUpload = null;
        $this->streamingAssistant = '';
        $this->isThinking = true;
        $this->turnIteration = 0;

        unset($this->activeChat);

        $this->dispatch('chat-list-changed');
        $this->dispatch('process-assistant-reply');
        $this->dispatch('composer-reset');
    }

    public function updatedImageUploads(ChatAttachmentService $service): void
    {
        if ($this->imageUploads === []) {
            return;
        }

        $owner = Auth::user();

        foreach ($this->imageUploads as $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            try {
                $payload = $service->storeImage($owner, $this->activeChatId, $upload);
            } catch (ValidationException $e) {
                $this->imageUploads = [];
                throw $e;
            }

            $this->persistedAttachments[] = $payload;
        }

        $this->imageUploads = [];
    }

    public function updatedVoiceUpload(ChatAttachmentService $service): void
    {
        if ($this->voiceUpload === null) {
            return;
        }

        $owner = Auth::user();

        // Drop any existing voice memo — only one per composer turn.
        $this->persistedAttachments = array_values(array_filter(
            $this->persistedAttachments,
            fn (array $att) => ($att['type'] ?? null) !== 'audio',
        ));

        try {
            $payload = $service->storeAudio($owner, $this->activeChatId, $this->voiceUpload);
        } catch (ValidationException $e) {
            $this->voiceUpload = null;
            throw $e;
        }

        $this->persistedAttachments[] = $payload;
        $this->voiceUpload = null;
    }

    public function removeAttachment(int $index): void
    {
        if (! isset($this->persistedAttachments[$index])) {
            return;
        }

        $att = $this->persistedAttachments[$index];
        $path = $att['path'] ?? null;
        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }

        unset($this->persistedAttachments[$index]);
        $this->persistedAttachments = array_values($this->persistedAttachments);
    }

    private function seedFromAttachments(): string
    {
        $first = $this->persistedAttachments[0] ?? null;
        if (! is_array($first)) {
            return (string) __('New chat');
        }

        return ($first['type'] ?? null) === 'audio'
            ? (string) __('Voice message')
            : (string) __('Image message');
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
            $this->addError('message', __('Connect Codex before sending a message.'));
            $this->finishTurn();

            return;
        } catch (CodexApiException $e) {
            if ($assembled !== '') {
                $sendMessage->saveAssistantMessage(
                    $chat,
                    $assembled."\n\n_(stream interrupted)_",
                    null,
                    $parentUserMessageId,
                );
            }
            $this->toast('danger', __('Codex API error').': '.$e->getMessage());
            report($e);
            $this->finishTurn();

            return;
        }

        // Persist text first so the order in DB matches the stream's order
        // (text typically precedes tool calls within one model response).
        if ($assembled !== '') {
            $sendMessage->saveAssistantMessage($chat, $assembled, null, $parentUserMessageId);
        }

        $statuses = [];
        foreach ($toolCallEvents as $event) {
            $payload = $sendMessage->dispatchToolCall(Auth::user(), $event, $chat);
            $sendMessage->saveAssistantMessage($chat, '', $payload, $parentUserMessageId);
            $statuses[] = (string) ($payload['status'] ?? '');
        }

        $this->streamingAssistant = '';
        $this->isThinking = false;
        unset($this->activeChat);

        $this->maybeContinueLoop($statuses, $toolCallEvents !== [], $assembled);
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
    /**
     * Short success toast keyed off the tool name that just confirmed. Falls
     * back to a generic "Done." so new tools work without UI changes.
     */
    private function toastForToolName(string $toolName): string
    {
        return match ($toolName) {
            'create_task' => __('Task created.'),
            'create_project' => __('Project created.'),
            'update_task_name', 'update_task_description', 'update_task_priority',
            'update_task_dates' => __('Task updated.'),
            'move_task_to_status' => __('Task moved.'),
            'assign_task' => __('Assignee added.'),
            'unassign_task' => __('Assignee removed.'),
            'archive_task' => __('Task archived.'),
            'unarchive_task' => __('Task unarchived.'),
            'start_timer' => __('Timer started.'),
            'stop_timer' => __('Timer stopped.'),
            'log_manual_time' => __('Time logged.'),
            'rename_project' => __('Project renamed.'),
            'add_project_status' => __('Status added.'),
            'rename_project_status' => __('Status renamed.'),
            'delete_project_status' => __('Status deleted.'),
            'bulk_move_tasks_to_status' => __('Tasks moved.'),
            'bulk_assign_tasks' => __('Tasks assigned.'),
            'bulk_archive_tasks' => __('Tasks archived.'),
            'bulk_delete_tasks' => __('Tasks deleted.'),
            'delete_task' => __('Task deleted.'),
            'delete_project' => __('Project deleted.'),
            default => __('Done.'),
        };
    }

    private function latestUserMessageId(Chat $chat): ?int
    {
        $latest = $chat->messages()
            ->where('role', \App\Domains\Chat\Enums\MessageRole::User->value)
            ->orderByDesc('id')
            ->value('id');

        return $latest !== null ? (int) $latest : null;
    }

    public function confirmToolCall(int $messageId, ConfirmToolCallService $confirm): void
    {
        $edits = $this->toolCallEdits[$messageId] ?? [];

        try {
            $payload = $confirm->execute(Auth::user(), $messageId, $edits);
        } catch (Throwable $e) {
            $this->toast('danger', __('Could not save').': '.$e->getMessage());
            report($e);

            return;
        }

        $status = (string) ($payload['status'] ?? '');
        $toolName = (string) ($payload['name'] ?? '');

        if ($status === 'created') {
            unset($this->toolCallEdits[$messageId], $this->toolCallLogoUploads[$messageId]);

            $this->toast('success', $this->toastForToolName($toolName));
            $this->dispatch('chat-list-changed');

            $this->resumeLoopIfReady();
        } elseif (! empty($payload['validation_errors'] ?? [])) {
            // Card stays pending; the partial re-renders with validation_errors
            // visible inline so the user can correct and retry.
            $this->toast('danger', __('Please fix the errors above.'));
        }

        unset($this->activeChat);
    }

    public function updatedToolCallLogoUploads(ChatAttachmentService $attachments): void
    {
        $owner = Auth::user();

        foreach ($this->toolCallLogoUploads as $messageId => $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $messageId = (int) $messageId;

            try {
                $payload = $attachments->storeImage($owner, $this->activeChatId, $upload);
            } catch (ValidationException $e) {
                $this->toolCallLogoUploads[$messageId] = null;
                throw $e;
            }

            if (! isset($this->toolCallEdits[$messageId])) {
                $this->toolCallEdits[$messageId] = [];
            }

            $this->toolCallEdits[$messageId]['logo_attachment'] = [
                'path' => $payload['path'],
                'url' => $payload['url'],
                'mime' => $payload['mime'],
                'name' => $payload['name'],
            ];

            $this->toolCallLogoUploads[$messageId] = null;
        }
    }

    public function clearToolCallLogo(int $messageId): void
    {
        if (! isset($this->toolCallEdits[$messageId])) {
            $this->toolCallEdits[$messageId] = [];
        }
        $this->toolCallEdits[$messageId]['logo_attachment'] = null;
    }

    public function answerChoice(
        int $messageId,
        int $optionIndex,
        AnswerChoiceService $answerChoice,
        SendMessageService $sendMessage,
    ): void {
        $chat = $this->activeChat;
        if ($chat === null) {
            return;
        }

        try {
            $label = $answerChoice->execute(Auth::user(), $messageId, $optionIndex);
        } catch (Throwable $e) {
            $this->toast('danger', __('Could not record your choice').': '.$e->getMessage());
            report($e);

            return;
        }

        // Post the chosen option as the user's next message and resume the
        // assistant stream so it can act on the answer.
        $sendMessage->saveUserMessage($chat, $label);

        // A user answer is a fresh turn — clear any prior stop request.
        if ($chat->stop_requested_at !== null) {
            $chat->forceFill(['stop_requested_at' => null])->save();
        }

        $this->streamingAssistant = '';
        $this->isThinking = true;
        $this->turnIteration = 0;

        unset($this->activeChat);

        $this->dispatch('process-assistant-reply');
    }

    public function discardToolCall(int $messageId, DiscardToolCallService $discard): void
    {
        try {
            $discard->execute(Auth::user(), $messageId);
            $this->toast('info', __('Discarded.'));
        } catch (Throwable $e) {
            $this->toast('danger', __('Could not discard').': '.$e->getMessage());
            report($e);
        }

        unset($this->activeChat);

        $this->resumeLoopIfReady();
    }

    /**
     * If there are no more pending tool-call cards in the current user turn,
     * resume the assistant so it can react to the resolved cards (write the
     * follow-up summary, kick off the next step, etc.). No-op if any pending
     * card remains.
     */
    private function resumeLoopIfReady(): void
    {
        $chat = $this->activeChat;
        if ($chat === null) {
            return;
        }

        $parentId = $this->latestUserMessageId($chat);
        if ($parentId === null) {
            return;
        }

        $stillPending = $chat->messages()
            ->where('parent_user_message_id', $parentId)
            ->whereNotNull('tool_call')
            ->get(['tool_call'])
            ->contains(fn (Message $m) => is_array($m->tool_call) && ($m->tool_call['status'] ?? null) === 'pending');

        if ($stillPending) {
            return;
        }

        $this->isThinking = true;
        $this->streamingAssistant = '';
        $this->dispatch('process-assistant-reply');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toolCardFor(Message $message): ?array
    {
        $toolCall = $message->tool_call;
        if (! is_array($toolCall)) {
            return null;
        }

        return app(AiToolCardPresenter::class)->present($toolCall, Auth::user());
    }

    public function render()
    {
        $this->seedToolCallEdits();

        return view('livewire.chat.chat-panel');
    }

    /**
     * Seed `toolCallEdits` from the LLM-proposed arguments for every pending
     * tool_call message that doesn't already have an in-flight edit entry.
     * Idempotent — preserves user typing across re-renders.
     */
    private function seedToolCallEdits(): void
    {
        foreach ($this->threadMessages as $message) {
            $toolCall = $message->tool_call;
            if (! is_array($toolCall) || ($toolCall['status'] ?? null) !== 'pending') {
                continue;
            }
            if (array_key_exists($message->id, $this->toolCallEdits)) {
                continue;
            }
            $this->toolCallEdits[$message->id] = is_array($toolCall['arguments'] ?? null)
                ? $toolCall['arguments']
                : [];
        }
    }
}
