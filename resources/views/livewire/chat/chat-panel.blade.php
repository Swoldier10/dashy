<div class="dashy-chat flex h-full min-h-0 flex-1 flex-col" wire:key="chat-panel">
    @if (! $this->isCodexConnected)
        @include('livewire.chat.partials.empty-codex')
    @elseif ($this->activeChat === null)
        @include('livewire.chat.partials.empty-greeting')
    @else
        @include('livewire.chat.partials.thread')
        @include('livewire.chat.partials.pinned-composer')
    @endif
</div>
