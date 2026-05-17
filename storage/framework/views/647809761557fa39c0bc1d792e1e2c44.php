<?php
    $rowId   = $mode === 'create' ? $item['cid']  : (string) $item->id;
    $rowName = $mode === 'create' ? $item['name'] : $item->name;
    $color   = "var({$category->colorVar()})";
    $renameMethod = $mode === 'create' ? 'renameBufferedStatus' : 'renameStatus';
    $deleteMethod = $mode === 'create' ? 'deleteBufferedStatus' : 'deleteStatus';
?>

<div
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'status-'.e($mode).'-'.e($rowId).''; ?>wire:key="status-<?php echo e($mode); ?>-<?php echo e($rowId); ?>"
    data-id="<?php echo e($rowId); ?>"
    class="group relative flex items-center gap-2 rounded-md px-2 py-1.5"
    style="background-color: var(--surface-2);"
>
    <span class="dashy-status-handle inline-flex shrink-0 cursor-grab p-1.5" aria-hidden="true" style="color: var(--ink-dim);">
        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'bars-3','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bars-3','class' => 'size-4']); ?>
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
    </span>
    <span class="size-3 shrink-0 rounded-full" style="background-color: <?php echo e($color); ?>;"></span>

    <input
        type="text"
        x-data="{ value: <?php echo \Illuminate\Support\Js::from($rowName)->toHtml() ?>, original: <?php echo \Illuminate\Support\Js::from($rowName)->toHtml() ?> }"
        x-model="value"
        x-on:blur="
            const trimmed = value.trim();
            if (trimmed !== '' && trimmed !== original) {
                $wire.<?php echo e($renameMethod); ?>(<?php echo \Illuminate\Support\Js::from($rowId)->toHtml() ?>, trimmed);
                original = trimmed;
            } else if (trimmed === '') {
                value = original;
            }
        "
        x-on:keydown.enter.prevent="$event.target.blur()"
        class="flex-1 bg-transparent text-sm outline-none <?php echo e($canManage ? 'pr-7' : ''); ?>"
        style="color: var(--ink);"
        maxlength="60"
        <?php if(! $canManage): echo 'disabled'; endif; ?>
        data-test="status-name-<?php echo e($mode); ?>-<?php echo e($rowId); ?>"
    />

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
        <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 opacity-0 transition group-hover:opacity-100 focus:opacity-100"
            style="color: var(--ink-dim);"
            wire:click="<?php echo e($deleteMethod); ?>(<?php echo \Illuminate\Support\Js::from($rowId)->toHtml() ?>)"
            onmouseover="this.style.color='var(--state-error)';"
            onmouseout="this.style.color='var(--ink-dim)';"
            aria-label="<?php echo e(__('Delete status')); ?>"
            data-test="status-delete-<?php echo e($mode); ?>-<?php echo e($rowId); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'trash','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'size-3.5']); ?>
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
</div>
<?php /**PATH /var/www/html/resources/views/livewire/partials/project-status-row.blade.php ENDPATH**/ ?>