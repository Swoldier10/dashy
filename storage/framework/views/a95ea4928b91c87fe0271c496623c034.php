<div
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?>wire:key="msg-<?php echo e($msg->id); ?>"
    class="space-y-3"
    style="color: var(--ink);"
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) $msg->content) !== ''): ?>
        <div class="dashy-prose text-[15px] leading-relaxed">
            <?php echo $markdown->render($msg->content); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->tool_call !== null): ?>
        <?php echo $__env->make('livewire.chat.partials.tool-call-card', [
            'message' => $msg,
            'card' => $this->toolCardFor($msg),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/messages/assistant-message.blade.php ENDPATH**/ ?>