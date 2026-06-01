<?php

namespace App\Livewire\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\CreateChatService;
use App\Domains\Chat\Services\FindChatForUserService;
use App\Domains\Chat\Services\SendMessageService;
use App\Domains\Chat\Services\UpdateChatStopStateService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Livewire\Chat\Concerns\HandlesAssistantStream;
use App\Livewire\Chat\Concerns\HandlesToolCalls;
use App\Livewire\Chat\Concerns\ManagesChatAttachments;
use App\Livewire\Chat\Concerns\PresentsChatGreeting;
use App\Livewire\Concerns\ResolvesCodexState;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ChatPanel extends Component
{
    use DispatchesDashyUi;
    use HandlesAssistantStream;
    use HandlesToolCalls;
    use ManagesChatAttachments;
    use PresentsChatGreeting;
    use ResolvesCodexState;
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

        $found = app(FindChatForUserService::class)->execute(Auth::user(), $chat);
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

        return app(FindChatForUserService::class)->execute(Auth::user(), $this->activeChatId);
    }

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function availableProjects(): Collection
    {
        return app(ListProjectsForUserService::class)->execute(Auth::user());
    }

    #[On('codex-connected')]
    public function refreshCodexConnection(): void
    {
        // Empty body — listener triggers re-render so isCodexConnected re-evaluates.
    }

    public function sendMessage(SendMessageService $sendMessage, CreateChatService $createChat): void
    {
        // Guard against double-submit / rapid re-fire while a turn is already
        // in flight — a second send would clobber the first's streaming state.
        // Every error/finish path resets isThinking, so this can't wedge.
        if ($this->isThinking) {
            return;
        }

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

        try {
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
                app(UpdateChatStopStateService::class)->clearStop(Auth::user(), $chat);
            }
        } catch (ValidationException $e) {
            // Rate limit / content-length / attachment validation — let Livewire
            // render it on the composer field instead of degrading to a toast.
            throw $e;
        } catch (Throwable $e) {
            $this->toast('danger', __('Something went wrong sending your message. Please try again.'));
            report($e);

            return;
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

    public function render()
    {
        $this->seedToolCallEdits();

        return view('livewire.chat.chat-panel');
    }
}
