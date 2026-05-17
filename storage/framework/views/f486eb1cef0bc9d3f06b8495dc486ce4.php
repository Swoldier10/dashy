<?php
    use App\Domains\Teams\Support\TeamColor;

    /**
     * @var iterable<\App\Domains\Teams\Models\Team> $teams
     * @var array<int,int> $teamCounts          // team_id => task count
     * @var int $totalCount                     // count for "Everything"
     * @var ?int $activeTeamId                  // null = Everything
     * @var string $everythingHref              // route('tasks')
     */
    $teams = $teams ?? collect();
    $teamCounts = $teamCounts ?? [];
    $totalCount = $totalCount ?? 0;
    $activeTeamId = $activeTeamId ?? null;
    $everythingHref = $everythingHref ?? route('tasks');
?>

<div class="sticky top-0 z-20 border-b"
     style="background-color: var(--bg); border-color: var(--border);"
     data-test="tasks-top-bar">
    
    <div class="flex items-center gap-3 px-4 py-3 lg:px-6">
        <span class="text-[11px] font-semibold uppercase tracking-wider"
              style="color: var(--ink-dim);"><?php echo e(__('Tasks')); ?></span>
        <span class="font-display text-base sm:text-lg" style="color: var(--ink);"><?php echo e(__('Workspace')); ?></span>

        <button
            type="button"
            x-on:click="document.querySelector('[data-test=sidebar-search]')?.focus()"
            class="ml-auto inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs transition"
            style="background-color: var(--surface); border-color: var(--border-mid); color: var(--ink-muted);"
            onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='var(--surface-2)';"
            onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='var(--surface)';"
            data-test="tasks-top-search"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'magnifying-glass','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'size-3.5']); ?>
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
            <span class="hidden sm:inline"><?php echo e(__('Search')); ?></span>
            <kbd class="hidden rounded px-1 text-[10px] font-medium sm:inline-block"
                 style="background-color: var(--surface-2); color: var(--ink-dim);"
                 aria-hidden="true">⌘K</kbd>
        </button>
    </div>

    
    <div class="flex items-center gap-1.5 overflow-x-auto px-4 pb-3 lg:px-6" data-test="tasks-team-chips">
        <a
            href="<?php echo e($everythingHref); ?>"
            wire:navigate
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition"
            @aria-current(($activeTeamId === null) ? 'page' : false)
            style="
                background-color: <?php echo e($activeTeamId === null ? 'var(--surface)' : 'transparent'); ?>;
                color: <?php echo e($activeTeamId === null ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                box-shadow: <?php echo e($activeTeamId === null
                    ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04)'
                    : 'none'); ?>;
            "
            <?php if($activeTeamId !== null): ?>
                onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
            <?php endif; ?>
            data-test="tasks-team-chip-everything"
        >
            <span class="font-display text-sm leading-none" style="color: <?php echo e($activeTeamId === null ? 'var(--ink)' : 'var(--ink-muted)'); ?>;">Σ</span>
            <span><?php echo e(__('Everything')); ?></span>
            <span class="text-[11px]" style="color: var(--ink-dim);"><?php echo e($totalCount); ?></span>
        </a>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $isActive = $activeTeamId === (int) $team->id;
                $count = (int) ($teamCounts[$team->id] ?? 0);
                $teamColorVar = TeamColor::for($team);
            ?>
            <a
                href="<?php echo e(route('tasks', ['team' => $team->id])); ?>"
                wire:navigate
                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'team-chip-'.e($team->id).''; ?>wire:key="team-chip-<?php echo e($team->id); ?>"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition"
                @aria-current($isActive ? 'page' : false)
                style="
                    background-color: <?php echo e($isActive ? 'var(--surface)' : 'transparent'); ?>;
                    color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                    box-shadow: <?php echo e($isActive ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04)' : 'none'); ?>;
                "
                <?php if(! $isActive): ?>
                    onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                <?php endif; ?>
                data-test="tasks-team-chip-<?php echo e($team->id); ?>"
            >
                <span class="inline-block size-1.5 rounded-full"
                      style="background-color: var(<?php echo e($teamColorVar); ?>);"></span>
                <span class="truncate"><?php echo e($team->name); ?></span>
                <span class="text-[11px]" style="color: var(--ink-dim);"><?php echo e($count); ?></span>
            </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

        <a
            href="<?php echo e(route('teams.index')); ?>"
            wire:navigate
            class="ml-auto inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition"
            style="color: var(--ink-muted);"
            onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='var(--surface-2)';"
            onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
            data-test="tasks-new-team"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'plus','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'size-3.5']); ?>
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
            <span><?php echo e(__('New team')); ?></span>
        </a>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/page-top-bar.blade.php ENDPATH**/ ?>