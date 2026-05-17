<?php
    use App\Domains\Tasks\Support\TaskDateFormatter;

    $plainText = $plainText ?? false;

    $isOverdue = $task->end_date !== null
        && $task->end_date->isPast()
        && (!$task->status || !in_array(
            $task->status->category?->value,
            [\App\Domains\Projects\Enums\ProjectStatusCategory::Done->value,
             \App\Domains\Projects\Enums\ProjectStatusCategory::Closed->value], true
        ));

    $startStr = $task->start_date?->toDateString();
    $endStr = $task->end_date?->toDateString();
    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();
    $nextWeek = now()->addWeek()->toDateString();
    $twoWeeks = now()->addWeeks(2)->toDateString();
    $fourWeeks = now()->addWeeks(4)->toDateString();
    $eightWeeks = now()->addWeeks(8)->toDateString();
?>

<?php if (isset($component)) { $__componentOriginal994b86018d055dff0caf70d6e2bc4725 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal994b86018d055dff0caf70d6e2bc4725 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.dropdown','data' => ['align' => 'end','position' => 'bottom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'end','position' => 'bottom']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('trigger', null, []); ?> 
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plainText): ?>
            <button
                type="button"
                wire:click.stop
                class="inline-flex h-5 items-center text-[11px] leading-4 transition focus:outline-none focus-visible:underline hover:underline"
                style="
                    color: <?php echo e($isOverdue ? 'var(--state-error)' : ($task->end_date ? 'var(--ink-muted)' : 'var(--ink-dim)')); ?>;
                "
                data-test="date-trigger-<?php echo e($task->id); ?>"
            >
                <span><?php echo e(TaskDateFormatter::format($task->end_date)); ?></span>
            </button>
        <?php else: ?>
            <button
                type="button"
                wire:click.stop
                class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 transition focus:outline-none focus-visible:ring-2"
                style="
                    color: <?php echo e($isOverdue ? 'var(--state-error)' : ($task->end_date ? 'var(--ink)' : 'var(--ink-dim)')); ?>;
                    --tw-ring-color: var(--blue);
                "
                data-test="date-trigger-<?php echo e($task->id); ?>"
            >
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'clock','class' => 'size-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'size-3 shrink-0']); ?>
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
                <span><?php echo e(TaskDateFormatter::format($task->end_date)); ?></span>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu','data' => ['class' => '!min-w-[280px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!min-w-[280px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="px-2 py-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider"
               style="color: var(--ink-dim);"><?php echo e(__('Due date')); ?></p>
        </div>

        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($today).'\')','dataTest' => 'date-quick-today-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($today).'\')','data-test' => 'date-quick-today-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('Today')); ?></span>
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
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($tomorrow).'\')','dataTest' => 'date-quick-tomorrow-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($tomorrow).'\')','data-test' => 'date-quick-tomorrow-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('Tomorrow')); ?></span>
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
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($nextWeek).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($nextWeek).'\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('Next week')); ?></span>
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
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($twoWeeks).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($twoWeeks).'\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('In 2 weeks')); ?></span>
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
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($fourWeeks).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($fourWeeks).'\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('In 4 weeks')); ?></span>
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
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($eightWeeks).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', \''.e($startStr ?? '').'\', \''.e($eightWeeks).'\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink);"><?php echo e(__('In 8 weeks')); ?></span>
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

        <?php if (isset($component)) { $__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.separator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2)): ?>
<?php $attributes = $__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2; ?>
<?php unset($__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2)): ?>
<?php $component = $__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2; ?>
<?php unset($__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click' => '$dispatch(\'task-detail:open\', { taskId: '.e($task->id).' })','dataTest' => 'date-open-detail-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click' => '$dispatch(\'task-detail:open\', { taskId: '.e($task->id).' })','data-test' => 'date-open-detail-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <span class="text-sm" style="color: var(--ink-muted);"><?php echo e(__('Pick custom date…')); ?></span>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->end_date): ?>
            <?php if (isset($component)) { $__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.separator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2)): ?>
<?php $attributes = $__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2; ?>
<?php unset($__attributesOriginal8fdc4ed814232d0e9c3c36d4be8a64b2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2)): ?>
<?php $component = $__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2; ?>
<?php unset($__componentOriginal8fdc4ed814232d0e9c3c36d4be8a64b2); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', null, null)','dataTest' => 'date-clear-'.e($task->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'button','wire:click.stop' => 'updateDates('.e($task->id).', null, null)','data-test' => 'date-clear-'.e($task->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <span class="text-sm" style="color: var(--state-error);"><?php echo e(__('Clear dates')); ?></span>
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
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/date-popover.blade.php ENDPATH**/ ?>