<header
    class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 border-b border-[var(--border)] bg-[var(--bg)] px-3 py-4 sm:px-5 lg:px-7 lg:py-5"
    data-test="calendar-toolbar"
>
    
    <div class="flex min-w-0 items-center gap-3">
        <span
            class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]"
            style="background-color: color-mix(in srgb, var(--state-success) 22%, white); color: var(--state-success-strong);"
        >
            <?php echo e(__('Calendar')); ?>

        </span>
        <h1
            class="truncate font-display text-[24px] font-medium leading-tight text-[var(--ink)] sm:text-[28px]"
            data-test="calendar-title"
        >
            <?php echo e($this->displayTitle); ?>

        </h1>

        <div class="ml-1 inline-flex items-center gap-0.5 text-[var(--ink-muted)]">
            <button
                type="button"
                wire:click="goToday"
                class="rounded-md px-2 py-1 text-[12px] font-medium transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-today"
            >
                <?php echo e(__('Today')); ?>

            </button>
            <button
                type="button"
                wire:click="prev"
                aria-label="<?php echo e(__('Previous')); ?>"
                class="inline-flex size-7 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-prev"
            >
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-left','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'size-4']); ?>
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
            <button
                type="button"
                wire:click="next"
                aria-label="<?php echo e(__('Next')); ?>"
                class="inline-flex size-7 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-next"
            >
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-right','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'size-4']); ?>
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
    </div>

    
    <div class="flex items-center gap-2">
        <div
            class="inline-flex rounded-full p-1"
            style="background-color: var(--surface-2);"
            data-test="calendar-view-switcher"
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['day' => __('Day'), 'week' => __('Week'), 'month' => __('Month')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $isActive = $view === $value; ?>
                <button
                    type="button"
                    wire:click="setView('<?php echo e($value); ?>')"
                    class="inline-flex h-7 items-center rounded-full px-3 text-[13px] font-medium transition"
                    style="
                        background-color: <?php echo e($isActive ? 'var(--cocoa)' : 'transparent'); ?>;
                        color: <?php echo e($isActive ? '#fff' : 'var(--ink-muted)'); ?>;
                    "
                    <?php if(! $isActive): ?>
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    data-test="calendar-view-<?php echo e($value); ?>"
                    aria-pressed="<?php echo e($isActive ? 'true' : 'false'); ?>"
                >
                    <?php echo e($label); ?>

                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <button
            type="button"
            wire:click="openCreate"
            class="dashy-btn dashy-btn--sm"
            style="background-color: transparent; color: var(--ink); border: 1px solid var(--border);"
            onmouseover="this.style.backgroundColor='var(--surface-2)';"
            onmouseout="this.style.backgroundColor='transparent';"
            data-test="calendar-add-event"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'plus','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'size-4']); ?>
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
            <span><?php echo e(__('New event')); ?></span>
        </button>
    </div>
</header>
<?php /**PATH /var/www/html/resources/views/livewire/calendar/partials/toolbar.blade.php ENDPATH**/ ?>