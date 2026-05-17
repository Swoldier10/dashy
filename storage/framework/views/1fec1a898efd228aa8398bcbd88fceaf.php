<?php
    use App\Domains\Teams\Support\TeamColor;

    /**
     * @var iterable<\App\Domains\Teams\Models\Team> $teams
     * @var array<int, \Illuminate\Support\Collection<int, \App\Domains\Projects\Models\Project>> $projectsByTeamId
     * @var array<int,int> $projectTaskCounts   // project_id => task count
     * @var int $totalCount                     // count for "Everything" (all teams)
     * @var ?int $activeProjectId               // null when on /tasks
     * @var bool $isEverythingActive            // highlights the "All …" entry
     * @var ?\App\Domains\Teams\Models\Team $activeTeam   // null when "Everything" tab is active
     * @var int $activeTeamCount                // count for the active team (used when $activeTeam set)
     */
    $teams = $teams ?? collect();
    $projectsByTeamId = $projectsByTeamId ?? [];
    $projectTaskCounts = $projectTaskCounts ?? [];
    $totalCount = $totalCount ?? 0;
    $activeProjectId = $activeProjectId ?? null;
    $isEverythingActive = $isEverythingActive ?? ($activeProjectId === null);
    $activeTeam = $activeTeam ?? null;
    $activeTeamCount = $activeTeamCount ?? 0;
    $teamProjects = $activeTeam ? ($projectsByTeamId[$activeTeam->id] ?? collect()) : collect();
?>

<aside class="flex w-full shrink-0 flex-col gap-2 md:w-56 lg:h-full lg:w-60 lg:overflow-y-auto lg:py-4 xl:w-64"
       data-test="workspace-sidebar">
    <div class="rounded-xl lg:flex-1"
         style="background-color: var(--surface-2); box-shadow: 0 1px 2px rgba(var(--ink-rgb), 0.04), 0 1px 0 0 var(--border) inset;">
        
        <div class="flex items-center border-b px-3 py-2.5"
             style="border-color: var(--border);">
            <span class="text-[11px] font-semibold uppercase tracking-wider"
                  style="color: var(--ink-dim);"><?php echo e(__('Projects')); ?></span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTeam): ?>
            
            <div class="p-2">
                <a
                    href="<?php echo e(route('tasks', ['team' => $activeTeam->id])); ?>"
                    wire:navigate
                    @aria-current($isEverythingActive ? 'page' : false)
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                    style="
                        background-color: <?php echo e($isEverythingActive ? 'var(--surface)' : 'transparent'); ?>;
                        color: <?php echo e($isEverythingActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                        box-shadow: <?php echo e($isEverythingActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none'); ?>;
                    "
                    <?php if(! $isEverythingActive): ?>
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    data-test="workspace-sidebar-everything"
                >
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md font-display text-sm"
                          style="background-color: var(--accent); color: var(--cocoa);">Σ</span>
                    <span class="min-w-0 flex-1 truncate"><?php echo e(__('All in team')); ?></span>
                    <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($activeTeamCount); ?></span>
                </a>

                <div class="mt-1 flex flex-col gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $teamProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $isActive = (int) $project->id === (int) $activeProjectId;
                            $count = (int) ($projectTaskCounts[$project->id] ?? 0);
                        ?>
                        <a
                            href="<?php echo e(route('tasks.show', $project)); ?>"
                            wire:navigate
                            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ws-project-'.e($project->id).''; ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ws-project-'.e($project->id).''; ?>wire:key="ws-project-<?php echo e($project->id); ?>"
                            @aria-current($isActive ? 'page' : false)
                            class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                            style="
                                background-color: <?php echo e($isActive ? 'var(--surface)' : 'transparent'); ?>;
                                color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                                box-shadow: <?php echo e($isActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none'); ?>;
                            "
                            <?php if(! $isActive): ?>
                                onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                            <?php endif; ?>
                            data-test="workspace-sidebar-project-<?php echo e($project->id); ?>"
                        >
                            <?php echo $__env->make('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'xs'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <span class="min-w-0 flex-1 truncate"><?php echo e($project->name); ?></span>
                            <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($count); ?></span>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    
                    <button
                        type="button"
                        wire:click="$dispatch('open-create-project', { teamId: <?php echo e($activeTeam->id); ?> })"
                        class="mt-1 flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                        style="color: var(--blue); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        data-test="workspace-sidebar-create-project-<?php echo e($activeTeam->id); ?>"
                    >
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md"
                              style="background-color: color-mix(in srgb, var(--blue) 14%, transparent);">
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
                        </span>
                        <span class="min-w-0 flex-1 truncate text-left"><?php echo e(__('New project')); ?></span>
                    </button>
                </div>
            </div>
        <?php else: ?>
            
            <div class="p-2">
                <a
                    href="<?php echo e(route('tasks')); ?>"
                    wire:navigate
                    @aria-current($isEverythingActive ? 'page' : false)
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                    style="
                        background-color: <?php echo e($isEverythingActive ? 'var(--surface)' : 'transparent'); ?>;
                        color: <?php echo e($isEverythingActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                        box-shadow: <?php echo e($isEverythingActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none'); ?>;
                    "
                    <?php if(! $isEverythingActive): ?>
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    <?php endif; ?>
                    data-test="workspace-sidebar-everything"
                >
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md font-display text-sm"
                          style="background-color: var(--accent); color: var(--cocoa);">Σ</span>
                    <span class="min-w-0 flex-1 truncate"><?php echo e(__('All tasks (Everything)')); ?></span>
                    <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($totalCount); ?></span>
                </a>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $groupProjects = $projectsByTeamId[$team->id] ?? collect();
                    $teamColorVar = TeamColor::for($team);
                ?>
                <div class="border-t px-2 py-2" style="border-color: var(--border);" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ws-team-'.e($team->id).''; ?>wire:key="ws-team-<?php echo e($team->id); ?>">
                    <div class="flex items-center gap-2 px-2 pb-1">
                        <span class="inline-block size-1.5 rounded-full" style="background-color: var(<?php echo e($teamColorVar); ?>);"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-wider"
                              style="color: var(--ink-dim);"><?php echo e($team->name); ?></span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groupProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $isActive = (int) $project->id === (int) $activeProjectId;
                                $count = (int) ($projectTaskCounts[$project->id] ?? 0);
                            ?>
                            <a
                                href="<?php echo e(route('tasks.show', $project)); ?>?from=everything"
                                wire:navigate
                                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ws-project-'.e($project->id).''; ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ws-project-'.e($project->id).''; ?>wire:key="ws-project-<?php echo e($project->id); ?>"
                                @aria-current($isActive ? 'page' : false)
                                class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                                style="
                                    background-color: <?php echo e($isActive ? 'var(--surface)' : 'transparent'); ?>;
                                    color: <?php echo e($isActive ? 'var(--ink)' : 'var(--ink-muted)'); ?>;
                                    box-shadow: <?php echo e($isActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none'); ?>;
                                "
                                <?php if(! $isActive): ?>
                                    onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                                <?php endif; ?>
                                data-test="workspace-sidebar-project-<?php echo e($project->id); ?>"
                            >
                                <?php echo $__env->make('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'xs'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <span class="min-w-0 flex-1 truncate"><?php echo e($project->name); ?></span>
                                <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($count); ?></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</aside>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/workspace-sidebar.blade.php ENDPATH**/ ?>