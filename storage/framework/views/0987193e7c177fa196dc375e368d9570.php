<?php
    $isChatRoute = $this->isChatRoute;
    $activeChatId = $this->activeChatId;
    $navItems = [
        ['route' => 'chat', 'label' => __('Chat'), 'icon' => 'chat-bubble-oval-left', 'active' => $this->activeSegment === 'chat'],
        ['route' => 'calendar', 'label' => __('Calendar'), 'icon' => 'calendar-days', 'active' => $this->activeSegment === 'calendar', 'dot' => true],
        ['route' => 'tasks', 'label' => __('Tasks'), 'icon' => 'clipboard-document-check', 'active' => $this->activeSegment === 'tasks'],
    ];
    $user = auth()->user();
?>

<div class="lg:w-[280px] lg:shrink-0">
    
    <header
        x-data="{ open: false }"
        class="sticky top-0 z-30 border-b lg:hidden"
        style="background-color: var(--surface-2); border-color: var(--border); color: var(--ink);"
        data-test="mobile-topbar"
    >
        <div class="flex items-center gap-3 px-3 py-3">
            <a href="<?php echo e(route('chat')); ?>" wire:navigate class="flex shrink-0 items-center gap-2" aria-label="Dashy home">
                <span
                    class="flex size-7 items-center justify-center rounded-md font-display text-sm font-semibold"
                    style="background-color: var(--cocoa); color: #fff;"
                    aria-hidden="true"
                >d</span>
                <span class="hidden sm:inline font-display text-base" style="color: var(--ink);">Dashy</span>
            </a>

            <label class="relative flex-1">
                <span class="sr-only"><?php echo e(__('Search')); ?></span>
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2','style' => 'color: var(--ink-muted);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2','style' => 'color: var(--ink-muted);']); ?>
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
                <input
                    type="search"
                    placeholder="<?php echo e(__('Search or jump to…')); ?>"
                    class="block w-full rounded-lg border py-2 pl-8 pr-3 text-sm transition focus:outline-none focus:ring-2"
                    style="background-color: var(--surface); border-color: var(--border-mid); color: var(--ink);"
                    aria-label="<?php echo e(__('Search or jump to')); ?>"
                    data-test="mobile-search"
                />
            </label>

            <button
                type="button"
                x-on:click="$store.modals.open('settings')"
                class="flex size-9 shrink-0 items-center justify-center rounded-full transition"
                style="background-color: var(--cocoa); color: #fff;"
                aria-label="<?php echo e(__('Open settings')); ?>"
                data-test="mobile-user-menu"
            >
                <span class="text-xs font-semibold"><?php echo e($user->initials()); ?></span>
            </button>
        </div>

        
        <div class="px-3 pb-2">
            <button
                type="button"
                x-on:click="open = !open"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition"
                style="background-color: var(--surface-2); color: var(--ink-muted);"
                :aria-expanded="open ? 'true' : 'false'"
                data-test="mobile-today-toggle"
            >
                <span class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'calendar-days','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'size-4']); ?>
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
                    <span><?php echo e($this->todayDateLabel); ?></span>
                </span>
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-down','class' => 'size-4 transition','xBind:class' => 'open ? \'rotate-180\' : \'\'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'size-4 transition','x-bind:class' => 'open ? \'rotate-180\' : \'\'']); ?>
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

            <div x-show="open" x-cloak x-transition class="mt-2 space-y-1" data-test="mobile-today">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->todayAgenda; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php echo $__env->make('livewire.partials.sidebar-agenda-row', ['row' => $row], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-1 py-2 text-xs" style="color: var(--ink-dim);"><?php echo e(__('Nothing scheduled today.')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isChatRoute): ?>
            <div class="flex gap-2 overflow-x-auto px-3 pb-3" data-test="mobile-recents">
                <button
                    type="button"
                    wire:click="startNewChat"
                    class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-2 text-sm font-medium transition"
                    style="background-color: var(--cocoa); color: #fff;"
                    data-test="mobile-new-chat"
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
                    <span><?php echo e(__('New')); ?></span>
                </button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->chats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $isActive = $activeChatId === $chat->id; ?>
                    <div
                        <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'mobile-chat-'.e($chat->id).''; ?>wire:key="mobile-chat-<?php echo e($chat->id); ?>"
                        class="flex shrink-0 items-center rounded-full pr-1.5"
                        style="background-color: <?php echo e($isActive ? 'var(--surface-2)' : 'var(--surface)'); ?>; border: 1px solid var(--border-mid);"
                    >
                        <a
                            href="<?php echo e(route('chat.show', $chat)); ?>"
                            wire:navigate
                            class="block max-w-[10rem] truncate px-3 py-2 text-sm"
                            style="color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;"
                        >
                            <?php echo e($chat->title ?? __('New chat')); ?>

                        </a>
                        <button
                            type="button"
                            wire:click.stop="confirmDeleteChat(<?php echo e($chat->id); ?>)"
                            class="rounded-full p-1 transition"
                            style="color: var(--ink-dim);"
                            onmouseover="this.style.color='var(--ink)';"
                            onmouseout="this.style.color='var(--ink-dim)';"
                            aria-label="<?php echo e(__('Delete chat')); ?>"
                            data-test="mobile-delete-chat-<?php echo e($chat->id); ?>"
                        >
                            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'x-mark','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-3.5']); ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-3 py-2 text-sm" style="color: var(--ink-dim);">
                        <?php echo e(__('No chats yet.')); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
    </header>

    
    <aside
        class="hidden h-screen w-full shrink-0 flex-col gap-4 border-r p-4 lg:sticky lg:top-0 lg:flex"
        style="background-color: var(--surface-2); border-color: var(--border); color: var(--ink);"
        data-test="desktop-sidebar"
    >
        
        <a href="<?php echo e(route('chat')); ?>" wire:navigate class="flex items-center gap-2 px-1" aria-label="Dashy home">
            <span
                class="flex size-7 items-center justify-center rounded-md font-display text-sm font-semibold"
                style="background-color: var(--cocoa); color: #fff;"
                aria-hidden="true"
            >d</span>
            <span class="font-display text-base" style="color: var(--ink);">Dashy</span>
        </a>

        
        <label class="relative block">
            <span class="sr-only"><?php echo e(__('Search')); ?></span>
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2','style' => 'color: var(--ink-muted);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2','style' => 'color: var(--ink-muted);']); ?>
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
            <input
                type="search"
                placeholder="<?php echo e(__('Search or jump to…')); ?>"
                class="block w-full rounded-lg border py-2 pl-8 pr-12 text-sm transition focus:outline-none focus:ring-2"
                style="background-color: var(--surface); border-color: var(--border-mid); color: var(--ink);"
                aria-label="<?php echo e(__('Search or jump to')); ?>"
                data-test="sidebar-search"
            />
            <kbd
                class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 rounded px-1.5 py-0.5 text-[10px] font-medium"
                style="background-color: var(--surface-2); color: var(--ink-dim);"
                aria-hidden="true"
            >⌘K</kbd>
        </label>

        
        <nav class="flex flex-col gap-0.5" role="navigation" aria-label="<?php echo e(__('Primary')); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    href="<?php echo e(route($item['route'])); ?>"
                    wire:navigate
                    aria-current="<?php echo e($item['active'] ? 'page' : 'false'); ?>"
                    class="flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm font-medium transition"
                    style="
                        color: <?php echo e($item['active'] ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                        background-color: <?php echo e($item['active'] ? 'var(--surface)' : 'transparent'); ?>;
                        box-shadow: <?php echo e($item['active']
                            ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04), 0 6px 14px -6px rgba(var(--ink-rgb), 0.10)'
                            : 'none'); ?>;
                    "
                    <?php if(! $item['active']): ?>
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    data-test="sidebar-nav-<?php echo e($item['route']); ?>"
                >
                    <span
                        class="flex size-7 shrink-0 items-center justify-center rounded-lg"
                        style="background-color: <?php echo e($item['active'] ? 'var(--accent)' : 'transparent'); ?>; color: <?php echo e($item['active'] ? 'var(--cocoa)' : 'currentColor'); ?>;"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $item['icon'],'class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon']),'class' => 'size-4']); ?>
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
                    <span class="flex-1"><?php echo e($item['label']); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['dot'])): ?>
                        <span class="size-1.5 rounded-full" style="background-color: var(--state-error);" aria-label="<?php echo e(__('Has updates')); ?>"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </nav>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isChatRoute): ?>
            <button
                type="button"
                wire:click="startNewChat"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
                style="background-color: var(--cocoa); color: #fff;"
                onmouseover="this.style.opacity='0.92'"
                onmouseout="this.style.opacity='1'"
                data-test="sidebar-new-chat"
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
                <span class="flex-1 text-left"><?php echo e(__('New chat')); ?></span>
                <kbd
                    class="rounded px-1.5 py-0.5 text-[10px] font-medium"
                    style="background-color: rgba(255, 255, 255, 0.16); color: rgba(255, 255, 255, 0.72);"
                    aria-hidden="true"
                >⌘N</kbd>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <section class="flex flex-col gap-2" data-test="sidebar-today">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                    <?php echo e($this->todayDateLabel); ?>

                </p>
                <a href="<?php echo e(route('calendar')); ?>" wire:navigate class="text-xs font-medium transition" style="color: var(--ink-muted);" onmouseover="this.style.color='var(--ink)'" onmouseout="this.style.color='var(--ink-muted)'"><?php echo e(__('View all')); ?></a>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->todayAgenda; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php echo $__env->make('livewire.partials.sidebar-agenda-row', ['row' => $row], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="px-1 text-xs" style="color: var(--ink-dim);"><?php echo e(__('Nothing scheduled today.')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        
        <div class="flex-1"></div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->chats->isNotEmpty()): ?>
            <section class="flex min-h-0 flex-col gap-1" data-test="sidebar-recents">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                    <?php echo e(__('Recent chats')); ?>

                </p>
                <div class="max-h-[180px] overflow-y-auto pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->chats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $isActive = $activeChatId === $chat->id; ?>
                        <div
                            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'sidebar-chat-'.e($chat->id).''; ?>wire:key="sidebar-chat-<?php echo e($chat->id); ?>"
                            class="group relative flex items-center rounded-lg transition"
                            style="background-color: <?php echo e($isActive ? 'var(--surface-2)' : 'transparent'); ?>;"
                            <?php if(! $isActive): ?>
                                onmouseover="this.style.backgroundColor='var(--surface-2)';"
                                onmouseout="this.style.backgroundColor='transparent';"
                            <?php endif; ?>
                        >
                            <a
                                href="<?php echo e(route('chat.show', $chat)); ?>"
                                wire:navigate
                                class="flex flex-1 items-center justify-between gap-2 truncate px-3 py-2 text-sm"
                                style="color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;"
                            >
                                <span class="truncate"><?php echo e($chat->title ?? __('New chat')); ?></span>
                                <span class="shrink-0 text-[11px]" style="color: var(--ink-dim);"><?php echo e($chat->updated_at?->diffForHumans(null, true, true)); ?></span>
                            </a>
                            <button
                                type="button"
                                wire:click.stop="confirmDeleteChat(<?php echo e($chat->id); ?>)"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded p-1 opacity-0 transition group-hover:opacity-100 focus:opacity-100"
                                style="color: var(--ink-dim); background-color: var(--surface);"
                                onmouseover="this.style.color='var(--ink)';"
                                onmouseout="this.style.color='var(--ink-dim)';"
                                aria-label="<?php echo e(__('Delete chat')); ?>"
                                data-test="sidebar-delete-chat-<?php echo e($chat->id); ?>"
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
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="px-3 py-2 text-sm" style="color: var(--ink-dim);">
                            <?php echo e(__('No chats yet.')); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="mt-2 border-t pt-3" style="border-color: var(--border);">
            <button
                type="button"
                x-on:click="$store.modals.open('settings')"
                class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition"
                style="background-color: transparent;"
                onmouseover="this.style.backgroundColor='var(--surface-2)'"
                onmouseout="this.style.backgroundColor='transparent'"
                data-test="sidebar-user-menu"
            >
                <span
                    class="flex size-8 shrink-0 items-center justify-center rounded-md text-xs font-semibold"
                    style="background-color: var(--cocoa); color: #fff;"
                ><?php echo e($user->initials()); ?></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium" style="color: var(--ink);"><?php echo e($user->name); ?></p>
                    <p class="truncate text-xs" style="color: var(--ink-muted);">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isCodexConnected): ?>
                            <?php echo e(__('Pro · :model', ['model' => $this->modelLabel])); ?>

                        <?php else: ?>
                            <?php echo e(__('Not connected')); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'cog-6-tooth','class' => 'size-4 shrink-0','style' => 'color: var(--ink-muted);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cog-6-tooth','class' => 'size-4 shrink-0','style' => 'color: var(--ink-muted);']); ?>
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
    </aside>

    
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'create-project','class' => 'max-w-4xl','wire:close' => 'cancelCreateProject','dataTest' => 'create-project-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'create-project','class' => 'max-w-4xl','wire:close' => 'cancelCreateProject','data-test' => 'create-project-modal']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form wire:submit="createProject" class="space-y-4">
            <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Create project')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $attributes = $__attributesOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $component = $__componentOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__componentOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'newProjectName','label' => __('Name'),'required' => true,'maxlength' => '80','dataTest' => 'create-project-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'newProjectName','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Name')),'required' => true,'maxlength' => '80','data-test' => 'create-project-name']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $attributes = $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $component = $__componentOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal4269bfd92fa85e2de78666dd35a6befa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.textarea','data' => ['wire:model' => 'newProjectDescription','label' => __('Description'),'rows' => '3','dataTest' => 'create-project-description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'newProjectDescription','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Description')),'rows' => '3','data-test' => 'create-project-description']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $attributes = $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $component = $__componentOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>

                    <div class="space-y-2">
                        <?php if (isset($component)) { $__componentOriginal92099487053ef6086efd6f50c4bedaee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92099487053ef6086efd6f50c4bedaee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.label','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Logo (optional)')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
                        <input
                            type="file"
                            wire:model="newProjectLogo"
                            accept="image/*"
                            class="dashy-input"
                            data-test="create-project-logo"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newProjectLogo): ?>
                            <img
                                src="<?php echo e($newProjectLogo->temporaryUrl()); ?>"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newProjectLogo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="dashy-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="dashy-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <?php echo $__env->make('livewire.partials.project-status-manager', [
                    'mode' => 'create',
                    'statusesByCategory' => $this->bufferedStatusesByCategory(),
                    'canManage' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="flex justify-end gap-2">
                <?php if (isset($component)) { $__componentOriginal2857dddf2ad6c0503130341fab495954 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2857dddf2ad6c0503130341fab495954 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal.close','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal.close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Cancel')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $attributes = $__attributesOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__attributesOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $component = $__componentOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__componentOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'submit','variant' => 'primary','dataTest' => 'confirm-create-project']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','data-test' => 'confirm-create-project']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Create')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
            </div>
        </form>
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

    
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'project-settings','class' => 'max-w-4xl','wire:close' => 'cancelProjectSettings','dataTest' => 'project-settings-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'project-settings','class' => 'max-w-4xl','wire:close' => 'cancelProjectSettings','data-test' => 'project-settings-modal']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form wire:submit="updateProject" class="space-y-4">
            <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Project settings')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $attributes = $__attributesOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $component = $__componentOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__componentOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'editProjectName','label' => __('Name'),'required' => true,'maxlength' => '80','dataTest' => 'settings-project-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editProjectName','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Name')),'required' => true,'maxlength' => '80','data-test' => 'settings-project-name']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $attributes = $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $component = $__componentOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal4269bfd92fa85e2de78666dd35a6befa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.textarea','data' => ['wire:model' => 'editProjectDescription','label' => __('Description'),'rows' => '3','dataTest' => 'settings-project-description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editProjectDescription','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Description')),'rows' => '3','data-test' => 'settings-project-description']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $attributes = $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $component = $__componentOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>

                    <div class="space-y-2">
                        <?php if (isset($component)) { $__componentOriginal92099487053ef6086efd6f50c4bedaee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92099487053ef6086efd6f50c4bedaee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.label','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Logo')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editProjectLogo): ?>
                            <img
                                src="<?php echo e($editProjectLogo->temporaryUrl()); ?>"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        <?php elseif($editProjectCurrentLogo): ?>
                            <img
                                src="<?php echo e($editProjectCurrentLogo); ?>"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <input
                            type="file"
                            wire:model="editProjectLogo"
                            accept="image/*"
                            class="dashy-input"
                            data-test="settings-project-logo"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editProjectLogo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="dashy-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="dashy-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <?php echo $__env->make('livewire.partials.project-status-manager', [
                    'mode' => 'edit',
                    'statusesByCategory' => $this->editProjectStatusesByCategory,
                    'canManage' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="flex justify-end gap-2">
                <?php if (isset($component)) { $__componentOriginal2857dddf2ad6c0503130341fab495954 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2857dddf2ad6c0503130341fab495954 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal.close','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal.close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Cancel')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $attributes = $__attributesOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__attributesOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $component = $__componentOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__componentOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'submit','variant' => 'primary','dataTest' => 'confirm-update-project']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','data-test' => 'confirm-update-project']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Save')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
            </div>
        </form>
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

    
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'confirm-project-deletion','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelDeleteProject']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-project-deletion','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelDeleteProject']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="space-y-4">
            <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Delete this project?')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $attributes = $__attributesOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $component = $__componentOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__componentOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginale626700ad092668e460de4abfec60854 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale626700ad092668e460de4abfec60854 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('This permanently removes the project. It cannot be undone.')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale626700ad092668e460de4abfec60854)): ?>
<?php $attributes = $__attributesOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__attributesOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale626700ad092668e460de4abfec60854)): ?>
<?php $component = $__componentOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__componentOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
            <div class="flex justify-end gap-2">
                <?php if (isset($component)) { $__componentOriginal2857dddf2ad6c0503130341fab495954 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2857dddf2ad6c0503130341fab495954 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal.close','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal.close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled','wire:click' => 'cancelDeleteProject']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled','wire:click' => 'cancelDeleteProject']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Cancel')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $attributes = $__attributesOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__attributesOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $component = $__componentOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__componentOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'danger','wire:click' => 'deleteProject','dataTest' => 'confirm-delete-project']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','wire:click' => 'deleteProject','data-test' => 'confirm-delete-project']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Delete')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
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

    
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'confirm-chat-deletion','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelDeleteChat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-chat-deletion','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelDeleteChat']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="space-y-4">
            <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Delete this chat?')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $attributes = $__attributesOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $component = $__componentOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__componentOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginale626700ad092668e460de4abfec60854 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale626700ad092668e460de4abfec60854 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('This permanently removes the chat and every message in it. It cannot be undone.')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale626700ad092668e460de4abfec60854)): ?>
<?php $attributes = $__attributesOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__attributesOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale626700ad092668e460de4abfec60854)): ?>
<?php $component = $__componentOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__componentOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
            <div class="flex justify-end gap-2">
                <?php if (isset($component)) { $__componentOriginal2857dddf2ad6c0503130341fab495954 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2857dddf2ad6c0503130341fab495954 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal.close','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal.close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled','wire:click' => 'cancelDeleteChat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled','wire:click' => 'cancelDeleteChat']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Cancel')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $attributes = $__attributesOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__attributesOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2857dddf2ad6c0503130341fab495954)): ?>
<?php $component = $__componentOriginal2857dddf2ad6c0503130341fab495954; ?>
<?php unset($__componentOriginal2857dddf2ad6c0503130341fab495954); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'danger','wire:click' => 'deleteChat','dataTest' => 'confirm-delete-chat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','wire:click' => 'deleteChat','data-test' => 'confirm-delete-chat']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Delete')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
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
</div>
<?php /**PATH /var/www/html/resources/views/livewire/app-sidebar.blade.php ENDPATH**/ ?>