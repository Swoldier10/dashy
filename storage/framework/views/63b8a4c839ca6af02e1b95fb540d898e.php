<?php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Generic single-target write card. Renders a one-line "what's about to
     * happen" summary plus Apply / Discard. Used by every confirm_write tool
     * whose proposal does not need user editing of fields (move, assign,
     * timer, priority, etc.). When the proposal DOES need a form (create_task,
     * create_project), the dedicated partial is used instead.
     */
    $status = $card['status'] ?? 'pending';
    $title = (string) ($card['title'] ?? __('Action'));
    $summary = (string) ($card['summary'] ?? '');
    $icon = (string) ($card['icon'] ?? 'pencil-square');
    $destructive = (bool) ($card['destructive'] ?? false);
    $errors = (array) ($card['validation_errors'] ?? []);
    $applyColor = $destructive ? 'var(--state-error)' : 'var(--blue)';
    $applyLabel = $destructive ? __('Delete') : __('Apply');
?>

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="<?php echo e($card['name'] ?? ''); ?>"
    data-mode="compact_write"
    data-status="<?php echo e($status); ?>"
>
    <div class="flex items-start gap-3 px-4 py-3">
        <div
            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full"
            style="background-color: var(--surface-3); color: <?php echo e($applyColor); ?>;"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $icon,'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold uppercase tracking-wide" style="color: var(--ink-muted);">
                <?php echo e($title); ?>

            </p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary !== ''): ?>
                <p class="mt-0.5 truncate text-sm" style="color: var(--ink);"><?php echo e($summary); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors !== []): ?>
                <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs" style="color: var(--state-error);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><?php echo e($error); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'pending'): ?>
                <button
                    type="button"
                    wire:click="discardToolCall(<?php echo e($message->id); ?>)"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-9 items-center justify-center rounded-full border px-3 py-1.5 text-xs font-medium"
                    style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                    data-test="compact-discard"
                >
                    <?php echo e(__('Discard')); ?>

                </button>
                <button
                    type="button"
                    wire:click="confirmToolCall(<?php echo e($message->id); ?>)"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-9 items-center justify-center rounded-full px-3 py-1.5 text-xs font-medium"
                    style="background-color: <?php echo e($applyColor); ?>; color: white;"
                    data-test="compact-apply"
                >
                    <?php echo e($applyLabel); ?>

                </button>
            <?php elseif($status === 'created'): ?>
                <span
                    class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium"
                    style="color: var(--state-success);"
                    data-test="compact-applied"
                >
                    <span aria-hidden="true">✓</span> <?php echo e(__('Applied')); ?>

                </span>
            <?php elseif($status === 'discarded'): ?>
                <span class="text-xs italic" style="color: var(--ink-dim);" data-test="compact-discarded">
                    <?php echo e(__('Discarded')); ?>

                </span>
            <?php elseif($status === 'failed'): ?>
                <span class="text-xs italic" style="color: var(--state-error);" data-test="compact-failed">
                    <?php echo e(__('Could not apply')); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/tool-cards/compact-write.blade.php ENDPATH**/ ?>