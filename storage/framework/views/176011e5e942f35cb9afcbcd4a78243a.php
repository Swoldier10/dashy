<div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?>wire:key="msg-<?php echo e($msg->id); ?>" class="flex justify-end">
    <div class="flex max-w-[85%] flex-col gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($msg->attachments)): ?>
            <div class="flex flex-wrap justify-end gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $msg->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($att['type'] ?? null) === 'image'): ?>
                        <a
                            href="<?php echo e($att['url'] ?? '#'); ?>"
                            target="_blank"
                            rel="noopener"
                            class="block max-w-[240px] overflow-hidden rounded-xl border"
                            style="border-color: var(--border-mid);"
                        >
                            <img
                                src="<?php echo e($att['url'] ?? ''); ?>"
                                alt="<?php echo e($att['name'] ?? ''); ?>"
                                class="h-auto w-full"
                                loading="lazy"
                            />
                        </a>
                    <?php elseif(($att['type'] ?? null) === 'audio'): ?>
                        <div class="flex max-w-[320px] flex-col gap-1">
                            <?php echo $__env->make('livewire.chat.partials.audio-bubble', [
                                'url' => $att['url'] ?? '',
                                'duration' => $att['duration_seconds'] ?? null,
                                'compact' => false,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($att['transcript'])): ?>
                                <p class="px-2 text-xs italic" style="color: var(--ink-muted);">
                                    <?php echo e($att['transcript']); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) $msg->content) !== ''): ?>
            <div
                class="rounded-2xl px-4 py-3 text-[15px] leading-relaxed"
                style="background-color: var(--surface); color: var(--ink);"
            >
                <div class="whitespace-pre-wrap break-words"><?php echo e($msg->content); ?></div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/messages/user-message.blade.php ENDPATH**/ ?>