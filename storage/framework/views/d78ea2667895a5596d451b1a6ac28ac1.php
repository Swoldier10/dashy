<?php
    $active = $this->activeEntry;
?>

<div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'running-timer-pill'; ?>wire:key="running-timer-pill">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($active && $active->task && $active->task->project): ?>
        <div
            class="fixed z-40 bottom-4 right-4 sm:top-4 sm:bottom-auto lg:top-4 lg:right-6"
            data-test="running-timer-pill"
        >
            <div
                class="flex items-center gap-2 rounded-full border px-3 py-1.5 shadow-lg backdrop-blur"
                style="
                    background-color: color-mix(in srgb, var(--surface) 88%, transparent);
                    border-color: var(--border-strong);
                    color: var(--ink);
                "
            >
                <span
                    class="inline-flex size-2 shrink-0 rounded-full"
                    style="background-color: var(--state-success);"
                    aria-hidden="true"
                ></span>

                <a
                    href="<?php echo e(route('tasks.show', $active->task->project_id)); ?>?task=<?php echo e($active->task_id); ?>"
                    class="flex min-w-0 items-center gap-2 text-sm transition hover:underline"
                    style="color: var(--ink);"
                    wire:navigate
                    data-test="running-timer-pill-link"
                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'play','class' => 'size-3.5 shrink-0','style' => 'color: var(--state-success);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'play','class' => 'size-3.5 shrink-0','style' => 'color: var(--state-success);']); ?>
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
                    <span class="max-w-[160px] truncate sm:max-w-[220px]" title="<?php echo e($active->task->name); ?>">
                        <?php echo e($active->task->name); ?>

                    </span>
                    <span
                        class="font-mono text-xs tabular-nums"
                        style="color: var(--ink-muted);"
                        x-data="{ startedAt: '<?php echo e($active->started_at?->toIso8601String()); ?>', value: 0, init() { this.tick(); this._t = setInterval(() => this.tick(), 1000); }, tick() { this.value = Math.max(0, Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000)); } }"
                        x-text="(function(s){const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),x=s%60;return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(x).padStart(2,'0');})(value)"
                        data-test="running-timer-pill-clock"
                    >00:00:00</span>
                </a>

                <button
                    type="button"
                    wire:click="stop"
                    aria-label="<?php echo e(__('Stop timer')); ?>"
                    class="inline-flex size-7 items-center justify-center rounded-full transition focus:outline-none focus-visible:ring-2"
                    style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                    onmouseover="this.style.color='var(--state-error)'; this.style.backgroundColor='color-mix(in srgb, var(--state-error) 12%, transparent)';"
                    onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                    data-test="running-timer-pill-stop"
                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'stop','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'stop','class' => 'size-3.5']); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/time-tracking/running-timer-pill.blade.php ENDPATH**/ ?>