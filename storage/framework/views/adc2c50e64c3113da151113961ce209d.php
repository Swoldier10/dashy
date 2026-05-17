<?php
    $colorVar = $status->category->colorVar();
    $count = $tasks->count();
    $hasTasks = $count > 0;
?>

<section
    class="flex flex-col gap-2"
    data-test="status-group-<?php echo e($status->id); ?>"
>
    <header class="flex items-center justify-between gap-2 px-1 py-1">
        <button
            type="button"
            wire:click="toggleStatusCollapse(<?php echo e($status->id); ?>)"
            class="flex items-center gap-2 rounded-md px-1 py-1 transition focus:outline-none focus-visible:ring-2"
            style="--tw-ring-color: var(--blue);"
            aria-expanded="<?php echo e($isCollapsed ? 'false' : 'true'); ?>"
            data-test="status-group-toggle-<?php echo e($status->id); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $isCollapsed ? 'chevron-right' : 'chevron-down','class' => 'size-3.5 shrink-0','style' => 'color: var(--ink-dim);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isCollapsed ? 'chevron-right' : 'chevron-down'),'class' => 'size-3.5 shrink-0','style' => 'color: var(--ink-dim);']); ?>
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
            <span
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                style="background-color: color-mix(in srgb, var(<?php echo e($colorVar); ?>) 14%, transparent); color: color-mix(in srgb, var(<?php echo e($colorVar); ?>) 80%, var(--ink));"
            >
                <span class="inline-block size-1.5 rounded-full" style="background-color: var(<?php echo e($colorVar); ?>);"></span>
                <span><?php echo e($status->name); ?></span>
            </span>
            <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($count); ?></span>
        </button>

        <button
            type="button"
            wire:click="openCreateTask(<?php echo e($status->id); ?>)"
            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs transition"
            style="color: var(--ink-dim);"
            onmouseover="this.style.color='var(--blue)'; this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)';"
            onmouseout="this.style.color='var(--ink-dim)'; this.style.backgroundColor='transparent';"
            data-test="status-add-task-<?php echo e($status->id); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'plus','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'size-3.5']); ?>
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
            <span><?php echo e(__('Add')); ?></span>
        </button>
    </header>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isCollapsed): ?>
        <div
            x-data
            x-init="
                if (window.Sortable) {
                    new window.Sortable($el, {
                        animation: 150,
                        group: 'tasks-<?php echo e($projectId); ?>',
                        handle: '.task-drag-handle',
                        draggable: '[data-task-id]',
                        onEnd: (evt) => {
                            const taskId = parseInt(evt.item.dataset.taskId, 10);
                            const fromStatus = parseInt(evt.from.dataset.statusId, 10);
                            const toStatus = parseInt(evt.to.dataset.statusId, 10);
                            const sourceIds = [...evt.from.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                            const targetIds = [...evt.to.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                            if (fromStatus === toStatus) {
                                $wire.reorderTasks(toStatus, targetIds);
                            } else {
                                $wire.moveTask(taskId, toStatus, sourceIds, targetIds);
                            }
                        },
                    });
                }
            "
            wire:ignore.self
            data-status-id="<?php echo e($status->id); ?>"
            data-test="status-sortable-<?php echo e($status->id); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex flex-col overflow-hidden',
                'rounded-xl' => $hasTasks,
            ]); ?>"
            style="
                min-height: <?php echo e($hasTasks ? '0' : '8px'); ?>;
                background-color: <?php echo e($hasTasks ? 'var(--surface)' : 'transparent'); ?>;
                box-shadow: <?php echo e($hasTasks
                    ? '0 1px 2px rgba(var(--ink-rgb), 0.04), 0 0 0 1px var(--border) inset'
                    : 'none'); ?>;
            "
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php echo $__env->make('livewire.tasks.partials.task-row', [
                    'task' => $task,
                    'teamMembers' => $teamMembers,
                    'allStatuses' => $allStatuses,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</section>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/status-group.blade.php ENDPATH**/ ?>