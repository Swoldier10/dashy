<?php
    use App\Domains\Projects\Enums\ProjectStatusCategory;
    $statusesByCategory = $allStatuses->groupBy(fn ($s) => $s->category->value);
    $currentStatus = $task->status;
    $currentColor = $currentStatus?->category?->colorVar() ?? '--ink-dim';
?>

<?php if (isset($component)) { $__componentOriginal994b86018d055dff0caf70d6e2bc4725 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal994b86018d055dff0caf70d6e2bc4725 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.dropdown','data' => ['align' => 'start','position' => 'bottom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'start','position' => 'bottom']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('trigger', null, []); ?> 
        <button
            type="button"
            wire:click.stop
            class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 font-medium transition focus:outline-none focus-visible:ring-2"
            style="
                background-color: color-mix(in srgb, var(<?php echo e($currentColor); ?>) 14%, transparent);
                color: color-mix(in srgb, var(<?php echo e($currentColor); ?>) 80%, var(--ink));
                --tw-ring-color: var(--blue);
            "
            data-test="status-trigger-<?php echo e($task->id); ?>"
        >
            <span class="inline-block size-1 shrink-0 rounded-full"
                  style="background-color: var(<?php echo e($currentColor); ?>);"></span>
            <span class="truncate"><?php echo e($currentStatus?->name ?? __('No status')); ?></span>
        </button>
     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu','data' => ['class' => '!min-w-[260px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!min-w-[260px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ProjectStatusCategory::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php $items = $statusesByCategory[$category->value] ?? collect(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->isNotEmpty()): ?>
                <div class="px-2 pt-1.5 pb-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wider"
                       style="color: var(--ink-dim);"><?php echo e($category->label()); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $isActive = $statusOption->id === $task->project_status_id; ?>
                    <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateStatus('.e($task->id).', '.e($statusOption->id).')','dataTest' => 'status-option-'.e($task->id).'-'.e($statusOption->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateStatus('.e($task->id).', '.e($statusOption->id).')','data-test' => 'status-option-'.e($task->id).'-'.e($statusOption->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <div class="flex w-full items-center gap-2">
                            <span class="inline-block size-2 shrink-0 rounded-full"
                                  style="background-color: var(<?php echo e($category->colorVar()); ?>);"></span>
                            <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                                <?php echo e($statusOption->name); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?>
                                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'check','class' => 'size-4 shrink-0','style' => 'color: var(--blue);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-4 shrink-0','style' => 'color: var(--blue);']); ?>
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php if (isset($__attributesOriginal994b86018d055dff0caf70d6e2bc4725)): ?>
<?php $attributes = $__attributesOriginal994b86018d055dff0caf70d6e2bc4725; ?>
<?php unset($__attributesOriginal994b86018d055dff0caf70d6e2bc4725); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal994b86018d055dff0caf70d6e2bc4725)): ?>
<?php $component = $__componentOriginal994b86018d055dff0caf70d6e2bc4725; ?>
<?php unset($__componentOriginal994b86018d055dff0caf70d6e2bc4725); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/status-popover.blade.php ENDPATH**/ ?>