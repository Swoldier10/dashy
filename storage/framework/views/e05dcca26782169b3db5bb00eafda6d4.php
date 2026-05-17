<?php
    // Reverse-enum order so most-advanced category (closed) shows first,
    // matching the tasks page status group order.
    $categories = array_reverse(\App\Domains\Projects\Enums\ProjectStatusCategory::cases());
    $reorderMethod = $mode === 'create' ? 'reorderBufferedStatuses' : 'reorderStatuses';
    $addMethod     = $mode === 'create' ? 'addBufferedStatus'       : 'addStatus';
?>

<div class="flex flex-col gap-4" data-test="project-status-manager-<?php echo e($mode); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php $items = $statusesByCategory[$category->value] ?? []; ?>
        <section data-category="<?php echo e($category->value); ?>">
            <header class="flex items-center justify-between pb-2">
                <h3 class="text-xs font-medium uppercase tracking-wider" style="color: var(--ink-dim);">
                    <?php echo e($category->label()); ?>

                </h3>
            </header>

            <div
                class="flex flex-col gap-1"
                x-data
                x-init="
                    if (window.Sortable) {
                        new window.Sortable($el, {
                            animation: 150,
                            handle: '.dashy-status-handle',
                            onEnd: () => {
                                const ids = [...$el.children].map(el => el.dataset.id).filter(Boolean);
                                $wire.<?php echo e($reorderMethod); ?>('<?php echo e($category->value); ?>', ids);
                            },
                        });
                    }
                "
                wire:ignore.self
                data-test="status-list-<?php echo e($mode); ?>-<?php echo e($category->value); ?>"
            >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php echo $__env->make('livewire.partials.project-status-row', [
                        'item' => $item,
                        'category' => $category,
                        'mode' => $mode,
                        'canManage' => $canManage,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                <div class="mt-1">
                    <input
                        type="text"
                        class="dashy-input w-full text-sm"
                        placeholder="<?php echo e(__('Add status')); ?>"
                        wire:model="pendingStatusName.<?php echo e($category->value); ?>"
                        wire:keydown.enter.prevent="<?php echo e($addMethod); ?>('<?php echo e($category->value); ?>')"
                        maxlength="60"
                        data-test="add-status-input-<?php echo e($mode); ?>-<?php echo e($category->value); ?>"
                    />
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/partials/project-status-manager.blade.php ENDPATH**/ ?>