<div class="m-auto flex max-w-md flex-col items-center gap-4 p-8 text-center">
    <div class="flex size-14 items-center justify-center rounded-2xl"
         style="background-color: color-mix(in srgb, var(--blue) 14%, transparent);">
        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'flag','class' => 'size-7','style' => 'color: var(--blue);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'flag','class' => 'size-7','style' => 'color: var(--blue);']); ?>
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
    <h2 class="font-display text-2xl" style="color: var(--ink);"><?php echo e(__('No statuses yet')); ?></h2>
    <p class="text-sm leading-relaxed" style="color: var(--ink-muted);">
        <?php echo e(__('Add at least one status to this project before you can create tasks. Open project settings from the sidebar.')); ?>

    </p>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/no-statuses-empty-state.blade.php ENDPATH**/ ?>