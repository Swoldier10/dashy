<?php
    /**
     * @var array<int, array{label:string, href:?string}> $breadcrumb
     * @var ?\App\Domains\Projects\Models\Project $project   // null on aggregator
     * @var string $title
     * @var bool $showArchived
     */
    $breadcrumb = $breadcrumb ?? [];
    $project = $project ?? null;
    $title = $title ?? __('All tasks');
    $showArchived = $showArchived ?? false;
?>

<div class="flex flex-col gap-2" data-test="page-heading">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($breadcrumb)): ?>
        <nav class="flex items-center gap-1.5 text-xs" style="color: var(--ink-dim);" aria-label="<?php echo e(__('Breadcrumb')); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $breadcrumb; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i > 0): ?>
                    <span aria-hidden="true">/</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($crumb['href'] ?? null): ?>
                    <a href="<?php echo e($crumb['href']); ?>" wire:navigate class="transition"
                       onmouseover="this.style.color='var(--ink)'" onmouseout="this.style.color='var(--ink-dim)'"
                    ><?php echo e($crumb['label']); ?></a>
                <?php else: ?>
                    <span style="color: var(--ink-muted);"><?php echo e($crumb['label']); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </nav>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex items-center gap-3">
        <div class="shrink-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project): ?>
                <span class="flex size-9 items-center justify-center rounded-lg"
                      style="background-color: var(--surface-2);">
                    <?php echo $__env->make('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'sm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </span>
            <?php else: ?>
                <span class="flex size-9 items-center justify-center rounded-lg font-display text-lg"
                      style="background-color: var(--surface-2); color: var(--ink);">Σ</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <h1 class="min-w-0 flex-1 truncate font-display text-xl sm:text-2xl" style="color: var(--ink); line-height: 1.2;" data-test="page-heading-title">
            <?php echo e($title); ?>

        </h1>
    </div>

    
    <div
        class="flex w-full items-center justify-end gap-1 rounded-lg border px-2 py-1"
        style="background-color: var(--surface-2); border-color: var(--border); box-shadow: 0 1px 2px rgba(var(--ink-rgb), 0.04);"
        data-test="page-heading-toolbar"
    >
        <?php if (isset($component)) { $__componentOriginal48049d68bb1dcc73e585cfc60414fdab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48049d68bb1dcc73e585cfc60414fdab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tooltip','data' => ['text' => $showArchived ? __('Hide archived') : __('Show archived'),'position' => 'bottom','align' => 'end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showArchived ? __('Hide archived') : __('Show archived')),'position' => 'bottom','align' => 'end']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'toggleArchivedVisibility','variant' => 'filled','size' => 'sm','iconOnly' => true,'icon' => $showArchived ? 'eye-slash' : 'eye','ariaLabel' => $showArchived ? __('Hide archived') : __('Show archived'),'dataTest' => 'tasks-toggle-archived']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'toggleArchivedVisibility','variant' => 'filled','size' => 'sm','iconOnly' => true,'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showArchived ? 'eye-slash' : 'eye'),'aria-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showArchived ? __('Hide archived') : __('Show archived')),'data-test' => 'tasks-toggle-archived']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
<?php if (isset($__attributesOriginal48049d68bb1dcc73e585cfc60414fdab)): ?>
<?php $attributes = $__attributesOriginal48049d68bb1dcc73e585cfc60414fdab; ?>
<?php unset($__attributesOriginal48049d68bb1dcc73e585cfc60414fdab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48049d68bb1dcc73e585cfc60414fdab)): ?>
<?php $component = $__componentOriginal48049d68bb1dcc73e585cfc60414fdab; ?>
<?php unset($__componentOriginal48049d68bb1dcc73e585cfc60414fdab); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal48049d68bb1dcc73e585cfc60414fdab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48049d68bb1dcc73e585cfc60414fdab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tooltip','data' => ['text' => __('New task'),'position' => 'bottom','align' => 'end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New task')),'position' => 'bottom','align' => 'end']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'openCreateTask','variant' => 'filled','size' => 'sm','iconOnly' => true,'icon' => 'plus','ariaLabel' => __('New task'),'dataTest' => 'tasks-header-add']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'openCreateTask','variant' => 'filled','size' => 'sm','iconOnly' => true,'icon' => 'plus','aria-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New task')),'data-test' => 'tasks-header-add']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
<?php if (isset($__attributesOriginal48049d68bb1dcc73e585cfc60414fdab)): ?>
<?php $attributes = $__attributesOriginal48049d68bb1dcc73e585cfc60414fdab; ?>
<?php unset($__attributesOriginal48049d68bb1dcc73e585cfc60414fdab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48049d68bb1dcc73e585cfc60414fdab)): ?>
<?php $component = $__componentOriginal48049d68bb1dcc73e585cfc60414fdab; ?>
<?php unset($__componentOriginal48049d68bb1dcc73e585cfc60414fdab); ?>
<?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/page-heading.blade.php ENDPATH**/ ?>