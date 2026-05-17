<?php
    use App\Domains\Projects\Enums\ProjectStatusCategory;
    use App\Domains\Projects\Support\ProjectColor;

    /**
     * @var \App\Domains\Tasks\Models\Task $task
     * @var iterable<\App\Models\User> $teamMembers
     * @var \Illuminate\Support\Collection<int, \App\Domains\Projects\Models\ProjectStatus> $allStatuses
     * @var bool $showProjectPill
     * @var bool $showStatusPill
     * @var bool $showCheckbox
     * @var bool $showDragHandle
     * @var bool $plainMeta  When true, project / date / priority render as inline plain text (no badge backgrounds).
     */
    $showProjectPill = $showProjectPill ?? false;
    $showStatusPill = $showStatusPill ?? true;
    $showCheckbox = $showCheckbox ?? true;
    $showDragHandle = $showDragHandle ?? true;
    $plainMeta = $plainMeta ?? false;

    $isComplete = $task->status && in_array(
        $task->status->category,
        [ProjectStatusCategory::Done, ProjectStatusCategory::Closed],
        true
    );

    $projectColorVar = $task->project ? ProjectColor::for($task->project) : '--ink-dim';
?>

<div
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'task-row-'.e($task->id).''; ?>wire:key="task-row-<?php echo e($task->id); ?>"
    data-task-id="<?php echo e($task->id); ?>"
    data-test="task-row-<?php echo e($task->id); ?>"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'group flex flex-wrap items-center gap-3 border-b px-4 py-2 transition last:border-b-0',
        'opacity-60' => $task->is_archived,
    ]); ?>"
    style="border-color: var(--border); background-color: transparent;"
    onmouseover="this.style.backgroundColor='var(--surface-2)'"
    onmouseout="this.style.backgroundColor='transparent'"
>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDragHandle): ?>
        <button
            type="button"
            class="task-drag-handle hidden cursor-grab opacity-0 transition group-hover:opacity-100 lg:inline-flex"
            style="color: var(--ink-dim);"
            aria-label="<?php echo e(__('Reorder')); ?>"
            wire:click.stop
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'bars-3','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bars-3','class' => 'size-3.5']); ?>
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
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCheckbox): ?>
        <div class="flex h-5 shrink-0 items-center">
            <button
                type="button"
                wire:click.stop="toggleComplete(<?php echo e($task->id); ?>)"
                class="dashy-task-checkbox cursor-pointer"
                aria-checked="<?php echo e($isComplete ? 'true' : 'false'); ?>"
                role="checkbox"
                aria-label="<?php echo e($isComplete ? __('Mark as not done') : __('Mark as done')); ?>"
                data-test="task-checkbox-<?php echo e($task->id); ?>"
            >
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'check','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-3']); ?>
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
            </button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex h-5 min-w-0 flex-1 items-center gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStatusPill): ?>
            <?php echo $__env->make('livewire.tasks.partials.status-popover', [
                'task' => $task,
                'allStatuses' => $allStatuses,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button
            type="button"
            wire:click="$dispatch('task-detail:open', { taskId: <?php echo e($task->id); ?> })"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'min-w-0 flex-1 truncate text-left text-xs leading-5 transition hover:underline',
                'line-through' => $isComplete,
            ]); ?>"
            style="color: <?php echo e($isComplete ? 'var(--ink-muted)' : 'var(--ink)'); ?>;"
            data-test="task-open-<?php echo e($task->id); ?>"
        >
            <?php echo e($task->name); ?>

        </button>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->is_archived): ?>
            <?php if (isset($component)) { $__componentOriginalcbd1924482a58fd71126719a902b0c12 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbd1924482a58fd71126719a902b0c12 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.badge','data' => ['dataTest' => 'task-row-archived-badge-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data-test' => 'task-row-archived-badge-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Archived')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbd1924482a58fd71126719a902b0c12)): ?>
<?php $attributes = $__attributesOriginalcbd1924482a58fd71126719a902b0c12; ?>
<?php unset($__attributesOriginalcbd1924482a58fd71126719a902b0c12); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbd1924482a58fd71126719a902b0c12)): ?>
<?php $component = $__componentOriginalcbd1924482a58fd71126719a902b0c12; ?>
<?php unset($__componentOriginalcbd1924482a58fd71126719a902b0c12); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showProjectPill): ?>
        <div class="flex h-5 w-32 shrink-0 items-center justify-end">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->project): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plainMeta): ?>
                    <a
                        href="<?php echo e(route('tasks.show', $task->project)); ?>"
                        wire:navigate
                        wire:click.stop
                        class="inline-flex h-5 items-center text-[11px] leading-4 transition hover:underline focus-visible:underline focus:outline-none"
                        style="color: var(--ink-muted);"
                        data-test="task-row-project-<?php echo e($task->id); ?>"
                        title="<?php echo e(__('Open :name', ['name' => $task->project->name])); ?>"
                    >
                        <span class="max-w-[8rem] truncate"><?php echo e($task->project->name); ?></span>
                    </a>
                <?php else: ?>
                    <a
                        href="<?php echo e(route('tasks.show', $task->project)); ?>"
                        wire:navigate
                        wire:click.stop
                        class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 transition"
                        style="
                            background-color: color-mix(in srgb, var(<?php echo e($projectColorVar); ?>) 14%, transparent);
                            color: color-mix(in srgb, var(<?php echo e($projectColorVar); ?>) 75%, var(--ink));
                        "
                        data-test="task-row-project-<?php echo e($task->id); ?>"
                        title="<?php echo e(__('Open :name', ['name' => $task->project->name])); ?>"
                    >
                        <span class="inline-block size-1 rounded-full" style="background-color: var(<?php echo e($projectColorVar); ?>);"></span>
                        <span class="max-w-[7rem] truncate"><?php echo e($task->project->name); ?></span>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex h-5 w-20 shrink-0 items-center justify-end">
        <?php echo $__env->make('livewire.tasks.partials.date-popover', ['task' => $task, 'plainText' => $plainMeta], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="flex h-5 w-16 shrink-0 items-center justify-end">
        <?php echo $__env->make('livewire.tasks.partials.priority-popover', ['task' => $task, 'plainText' => $plainMeta], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    
    <div class="shrink-0">
        <?php if (isset($component)) { $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.popover','data' => ['align' => 'end','position' => 'bottom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'end','position' => 'bottom']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('trigger', null, []); ?> 
                <button
                    type="button"
                    :class="open ? '!opacity-100' : ''"
                    class="inline-flex size-7 items-center justify-center rounded-md opacity-100 transition lg:opacity-0 lg:group-hover:opacity-100 focus-visible:opacity-100 focus:outline-none focus-visible:ring-2"
                    style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                    onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='rgba(var(--ink-rgb), 0.06)';"
                    onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                    aria-label="<?php echo e(__('Task actions')); ?>"
                    data-test="task-row-actions-<?php echo e($task->id); ?>"
                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'ellipsis-horizontal','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ellipsis-horizontal','class' => 'size-4']); ?>
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
                </button>
             <?php $__env->endSlot(); ?>

            <?php if (isset($component)) { $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->is_archived): ?>
                    <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['icon' => 'archive-box-x-mark','wire:click' => 'unarchiveTask('.e($task->id).')','dataTest' => 'task-row-unarchive-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'archive-box-x-mark','wire:click' => 'unarchiveTask('.e($task->id).')','data-test' => 'task-row-unarchive-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Unarchive')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $attributes = $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $component = $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['icon' => 'archive-box-arrow-down','wire:click' => 'archiveTask('.e($task->id).')','dataTest' => 'task-row-archive-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'archive-box-arrow-down','wire:click' => 'archiveTask('.e($task->id).')','data-test' => 'task-row-archive-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Archive')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $attributes = $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $component = $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['icon' => 'trash','wire:click' => 'deleteTask('.e($task->id).')','dataTest' => 'task-row-delete-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'trash','wire:click' => 'deleteTask('.e($task->id).')','data-test' => 'task-row-delete-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Delete')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $attributes = $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $component = $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6)): ?>
<?php $attributes = $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6; ?>
<?php unset($__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6)): ?>
<?php $component = $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6; ?>
<?php unset($__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $attributes = $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $component = $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/task-row-card.blade.php ENDPATH**/ ?>