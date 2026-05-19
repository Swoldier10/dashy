@php
    use App\Domains\Chat\Enums\MessageRole;
    use App\Domains\Chat\Services\MarkdownRenderer;
    $markdown = app(MarkdownRenderer::class);
@endphp
<div
    class="flex-1 overflow-y-auto"
    wire:key="thread-{{ $activeChatId }}"
    x-data
    x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
    x-on:livewire-update.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
>
    <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
        <div class="space-y-8">
            @foreach ($this->threadMessages as $msg)
                @if ($msg->role === MessageRole::User)
                    @include('livewire.chat.partials.messages.user-message', ['msg' => $msg])
                @else
                    @include('livewire.chat.partials.messages.assistant-message', ['msg' => $msg, 'markdown' => $markdown])
                @endif
            @endforeach

            {{-- Streaming bubble --}}
            @if ($streamingAssistant !== '' || $isThinking)
                @include('livewire.chat.partials.messages.streaming-bubble')
            @endif
        </div>
    </div>
</div>
