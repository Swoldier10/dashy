<?php
use Livewire\Component;
?>

<div>
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'settings','size' => 'xl','style' => 'padding: 0; overflow: hidden;','dataTest' => 'settings-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'settings','size' => 'xl','style' => 'padding: 0; overflow: hidden;','data-test' => 'settings-modal']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div
            class="dashy-settings-modal grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)]"
            style="height: min(640px, calc(100dvh - 64px));"
        >
            
            <nav
                class="flex flex-col gap-1 border-b p-3 md:border-b-0 md:border-r md:p-4"
                style="border-color: var(--border); background-color: var(--surface-2);"
                aria-label="<?php echo e(__('Settings sections')); ?>"
            >
                <p
                    class="hidden px-2 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider md:block"
                    style="color: var(--ink-dim);"
                >
                    <?php echo e(__('Settings')); ?>

                </p>

                <ul
                    role="tablist"
                    aria-orientation="vertical"
                    class="flex gap-1 overflow-x-auto md:flex-col md:overflow-visible"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->sections(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $isActive = $section === $key;
                        ?>
                        <li class="shrink-0 md:shrink">
                            <button
                                type="button"
                                role="tab"
                                wire:click="setSection('<?php echo e($key); ?>')"
                                aria-selected="<?php echo e($isActive ? 'true' : 'false'); ?>"
                                aria-controls="settings-section-pane"
                                data-test="settings-tab-<?php echo e($key); ?>"
                                data-active="<?php echo e($isActive ? 'true' : 'false'); ?>"
                                class="flex min-h-[40px] w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                                style="
                                    background-color: <?php echo e($isActive ? 'var(--surface)' : 'transparent'); ?>;
                                    color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                                    box-shadow: <?php echo e($isActive ? '0 1px 0 0 rgba(var(--ink-rgb), 0.04)' : 'none'); ?>;
                                "
                                <?php if(! $isActive): ?>
                                    onmouseover="this.style.color='var(--ink)';"
                                    onmouseout="this.style.color='var(--ink-muted)';"
                                <?php endif; ?>
                            >
                                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $meta['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($meta['icon']),'class' => 'size-4']); ?>
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
                                <span><?php echo e($meta['label']); ?></span>
                            </button>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>

                
                <form
                    method="POST"
                    action="<?php echo e(route('logout')); ?>"
                    class="mt-auto hidden md:block"
                >
                    <?php echo csrf_field(); ?>
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                        style="color: var(--state-error); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        data-test="settings-logout-button"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'arrow-right-start-on-rectangle','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-start-on-rectangle','class' => 'size-4']); ?>
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
                        <span><?php echo e(__('Log out')); ?></span>
                    </button>
                </form>
            </nav>

            
            <div class="flex min-h-0 flex-col">
                <header
                    class="flex items-center justify-between gap-3 border-b px-5 py-4 md:px-6"
                    style="border-color: var(--border);"
                >
                    <h2 class="font-display text-lg" style="color: var(--ink);">
                        <?php echo e($this->sections()[$section]['label']); ?>

                    </h2>
                    <button
                        type="button"
                        x-on:click="$store.modals.close('settings')"
                        class="flex size-9 items-center justify-center rounded-full transition"
                        style="color: var(--ink-muted); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface-2)';this.style.color='var(--ink)'"
                        onmouseout="this.style.backgroundColor='transparent';this.style.color='var(--ink-muted)'"
                        aria-label="<?php echo e(__('Close settings')); ?>"
                        data-test="settings-close-button"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'x-mark','class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-5']); ?>
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
                </header>

                <div
                    id="settings-section-pane"
                    role="tabpanel"
                    class="dashy-settings-pane flex-1 overflow-y-auto px-5 py-5 md:px-6"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($section):
                        case ('profile'): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.profile-section', []);

$__keyOuter = $__key ?? null;

$__key = 'settings-profile';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3822792930-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php break; ?>
                        <?php case ('appearance'): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.appearance-section', []);

$__keyOuter = $__key ?? null;

$__key = 'settings-appearance';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3822792930-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php break; ?>
                        <?php case ('security'): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.security-section', []);

$__keyOuter = $__key ?? null;

$__key = 'settings-security';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3822792930-2', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php break; ?>
                        <?php case ('integrations'): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.integrations-section', []);

$__keyOuter = $__key ?? null;

$__key = 'settings-integrations';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3822792930-3', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php break; ?>
                        <?php case ('memory'): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.memory-section', []);

$__keyOuter = $__key ?? null;

$__key = 'settings-memory';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3822792930-4', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php break; ?>
                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <form
                    method="POST"
                    action="<?php echo e(route('logout')); ?>"
                    class="border-t px-5 py-3 md:hidden"
                    style="border-color: var(--border);"
                >
                    <?php echo csrf_field(); ?>
                    <button
                        type="submit"
                        class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg text-sm font-medium"
                        style="color: var(--state-error); background-color: transparent;"
                        data-test="settings-logout-button-mobile"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'arrow-right-start-on-rectangle','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-start-on-rectangle','class' => 'size-4']); ?>
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
                        <span><?php echo e(__('Log out')); ?></span>
                    </button>
                </form>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f3ea2574b5a945d549c436557b459c9)): ?>
<?php $attributes = $__attributesOriginal6f3ea2574b5a945d549c436557b459c9; ?>
<?php unset($__attributesOriginal6f3ea2574b5a945d549c436557b459c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f3ea2574b5a945d549c436557b459c9)): ?>
<?php $component = $__componentOriginal6f3ea2574b5a945d549c436557b459c9; ?>
<?php unset($__componentOriginal6f3ea2574b5a945d549c436557b459c9); ?>
<?php endif; ?>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/f55c9bdb.blade.php ENDPATH**/ ?>