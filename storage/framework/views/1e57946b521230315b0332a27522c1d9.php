<?php
    $isChatHome = request()->routeIs('chat', 'chat.show');
    $isCalendar = request()->routeIs('calendar');
    $isTasks = request()->routeIs('tasks*');

    $tabs = [
        [
            'label' => __('Chat home'),
            'icon' => 'home',
            'href' => route('chat'),
            'active' => $isChatHome,
        ],
        [
            'label' => __('Calendar'),
            'icon' => 'calendar-days',
            'href' => route('calendar'),
            'active' => $isCalendar,
        ],
        [
            'label' => __('Tasks'),
            'icon' => 'clipboard-document-check',
            'href' => route('tasks'),
            'active' => $isTasks,
        ],
        [
            'label' => __('Settings'),
            'icon' => 'cog-6-tooth',
            'onclick' => "\$store.modals.open('settings')",
            'active' => false,
            'isSeparated' => true,
        ],
    ];
?>

<div
    class="pointer-events-none fixed inset-x-3 bottom-3 z-40 flex justify-center md:inset-x-0 lg:left-[280px]"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    data-test="bottom-tab-bar"
>
    <nav
        role="navigation"
        aria-label="<?php echo e(__('Primary')); ?>"
        class="pointer-events-auto flex w-full max-w-md items-center justify-around gap-1 rounded-full border p-2 shadow-lg md:w-auto md:max-w-none md:justify-center md:gap-2 md:px-4 md:py-1.5"
        style="background-color: var(--surface); border-color: var(--border); box-shadow: 0 10px 30px -12px rgba(var(--ink-rgb), 0.18);"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $isActive = (bool) $tab['active'];
                $isDisabled = (bool) ($tab['disabled'] ?? false);
                $isSeparated = (bool) ($tab['isSeparated'] ?? false);
                $hasOnClick = ! empty($tab['onclick']);
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSeparated): ?>
                <span class="hidden md:block h-5 w-px mx-1" style="background-color: var(--border);" aria-hidden="true"></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php
                $slug = \Illuminate\Support\Str::slug($tab['label']);
                $attrs = 'data-test="bottom-tab-'.$slug.'" data-active="'.($isActive ? 'true' : 'false').'"';
                $sharedClasses = 'flex min-h-[44px] flex-1 items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition md:min-h-0 md:flex-none md:px-3.5 md:py-1.5 md:text-sm';
                $sharedStyle = 'background-color: '.($isActive ? 'var(--cocoa)' : 'transparent').'; color: '.($isActive ? '#fff' : 'var(--ink-muted)').';';
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDisabled): ?>
                <span
                    aria-disabled="true"
                    class="flex min-h-[44px] flex-1 items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium opacity-40 md:min-h-0 md:flex-none md:px-3.5 md:py-1.5 md:text-sm"
                    style="color: var(--ink-muted);"
                    <?php echo $attrs; ?>

                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $tab['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tab['icon']),'class' => 'size-4']); ?>
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
                    <span class="hidden md:inline"><?php echo e($tab['label']); ?></span>
                </span>
            <?php elseif($hasOnClick): ?>
                <button
                    type="button"
                    x-on:click="<?php echo e($tab['onclick']); ?>"
                    class="<?php echo e($sharedClasses); ?>"
                    style="<?php echo e($sharedStyle); ?>"
                    <?php if(! $isActive): ?>
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    <?php echo $attrs; ?>

                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $tab['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tab['icon']),'class' => 'size-4']); ?>
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
                    <span class="hidden md:inline"><?php echo e($tab['label']); ?></span>
                </button>
            <?php else: ?>
                <a
                    href="<?php echo e($tab['href']); ?>"
                    wire:navigate
                    aria-current="<?php echo e($isActive ? 'page' : 'false'); ?>"
                    class="<?php echo e($sharedClasses); ?>"
                    style="<?php echo e($sharedStyle); ?>"
                    <?php if(! $isActive): ?>
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    <?php echo $attrs; ?>

                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $tab['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tab['icon']),'class' => 'size-4']); ?>
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
                    <span class="hidden md:inline"><?php echo e($tab['label']); ?></span>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </nav>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/bottom-tab-bar.blade.php ENDPATH**/ ?>