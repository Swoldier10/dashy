<x-layouts::app :title="__('Chat')">
    <livewire:chat.chat-panel :chat="request()->route('chat')" />
</x-layouts::app>
