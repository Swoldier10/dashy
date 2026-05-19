<div class="shrink-0 px-3 pb-4 pt-2 sm:px-4 sm:pb-5">
    <div class="mx-auto w-full max-w-3xl">
        @include('livewire.chat.partials.composer', ['large' => false])
        <p class="mt-2 text-center text-xs" style="color: var(--ink-dim);">
            {{ __('Codex can make mistakes. Verify important details.') }}
        </p>
    </div>
</div>
