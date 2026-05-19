<div
    wire:key="msg-{{ $msg->id }}"
    class="space-y-3"
    style="color: var(--ink);"
>
    @if (trim((string) $msg->content) !== '')
        <div class="dashy-prose text-[15px] leading-relaxed">
            {!! $markdown->render($msg->content) !!}
        </div>
    @endif

    @if ($msg->tool_call !== null)
        @include('livewire.chat.partials.tool-call-card', [
            'message' => $msg,
            'card' => $this->toolCardFor($msg),
        ])
    @endif
</div>
