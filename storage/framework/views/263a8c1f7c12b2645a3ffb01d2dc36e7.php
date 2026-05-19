<div
    class="text-[15px] leading-relaxed"
    style="color: var(--ink);"
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'streaming-'.e($activeChatId).''; ?>wire:key="streaming-<?php echo e($activeChatId); ?>"
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($streamingAssistant !== ''): ?>
        <div class="whitespace-pre-wrap break-words" wire:stream="streamingAssistant"><?php echo e($streamingAssistant); ?></div>
    <?php else: ?>
        <div class="flex items-center gap-1.5 py-2">
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:0ms]"
                style="background-color: var(--ink-muted);"
            ></span>
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:150ms]"
                style="background-color: var(--ink-muted);"
            ></span>
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:300ms]"
                style="background-color: var(--ink-muted);"
            ></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/messages/streaming-bubble.blade.php ENDPATH**/ ?>