<?php

namespace App\Livewire\Chat\Concerns;

use App\Domains\Chat\Ai\Services\AiToolCardPresenter;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\AnswerChoiceService;
use App\Domains\Chat\Services\ConfirmToolCallService;
use App\Domains\Chat\Services\DiscardToolCallService;
use App\Domains\Chat\Services\ListPendingToolCallsForTurnService;
use App\Domains\Chat\Services\SendMessageService;
use App\Domains\Chat\Services\UpdateChatStopStateService;
use Illuminate\Support\Facades\Auth;
use Throwable;

trait HandlesToolCalls
{
    /**
     * In-flight edits for each pending tool_call message, keyed by message id.
     * Seeded from the LLM-proposed arguments on first render; live-bound to
     * the inputs in the card partial; passed to ConfirmToolCallService on
     * Create.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $toolCallEdits = [];

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
            app(UpdateChatStopStateService::class)->clearStop(Auth::user(), $chat);
        }

        $this->streamingAssistant = '';
        $this->isThinking = true;
        $this->turnIteration = 0;

        unset($this->activeChat);

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

    /**
     * Seed `toolCallEdits` from the LLM-proposed arguments for every pending
     * tool_call message that doesn't already have an in-flight edit entry.
     * Idempotent — preserves user typing across re-renders.
     */
    protected function seedToolCallEdits(): void
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

        $stillPending = app(ListPendingToolCallsForTurnService::class)
            ->execute($chat, $parentId)
            ->contains(fn (Message $m) => is_array($m->tool_call) && ($m->tool_call['status'] ?? null) === 'pending');

        if ($stillPending) {
            return;
        }

        $this->isThinking = true;
        $this->streamingAssistant = '';
        $this->dispatch('process-assistant-reply');
    }

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
}
