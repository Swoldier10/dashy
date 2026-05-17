<?php
    use App\Domains\Tasks\Enums\TaskPriority;
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];
    $priorityOptions = [];
    foreach ($priorities as $priorityOption) {
        $priorityOptions[$priorityOption->value] = $priorityOption->label();
    }
    $statusOptions = $this->statuses->mapWithKeys(fn ($s) => [(string) $s->id => $s->name])->all();
?>

<?php if (isset($component)) { $__componentOriginal7b48427f97b7da498a55d72398ac1330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b48427f97b7da498a55d72398ac1330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.drawer','data' => ['name' => 'task-create','side' => 'right','size' => 'lg','class' => 'md:!max-w-[820px]','focusable' => true,'wire:close' => 'closeCreateTask']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'task-create','side' => 'right','size' => 'lg','class' => 'md:!max-w-[820px]','focusable' => true,'wire:close' => 'closeCreateTask']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <form wire:submit.prevent="submitCreateTask" class="flex flex-col gap-6 p-6" data-test="task-create-drawer">
        
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                    style="background-color: color-mix(in srgb, var(--blue) 18%, transparent); color: var(--blue);"
                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'plus','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'size-3']); ?>
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
                    <span><?php echo e(__('New task')); ?></span>
                </span>
                <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($this->project->name); ?></span>
            </div>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'createName','label' => __('Name'),'placeholder' => __('What needs to be done?'),'maxlength' => '200','required' => true,'dataTest' => 'task-create-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'createName','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Name')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('What needs to be done?')),'maxlength' => '200','required' => true,'data-test' => 'task-create-name']); ?>
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

        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <?php if (isset($component)) { $__componentOriginal18e1f2087a35ad7f1de327d753761793 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18e1f2087a35ad7f1de327d753761793 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.searchable-select','data' => ['wire:model' => 'createStatusId','label' => __('Status'),'options' => $statusOptions,'placeholder' => __('Select a status'),'searchPlaceholder' => __('Search statuses…'),'emptyMessage' => __('No statuses match your search.'),'dataTest' => 'task-create-status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'createStatusId','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusOptions),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select a status')),'searchPlaceholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search statuses…')),'emptyMessage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No statuses match your search.')),'data-test' => 'task-create-status']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18e1f2087a35ad7f1de327d753761793)): ?>
<?php $attributes = $__attributesOriginal18e1f2087a35ad7f1de327d753761793; ?>
<?php unset($__attributesOriginal18e1f2087a35ad7f1de327d753761793); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18e1f2087a35ad7f1de327d753761793)): ?>
<?php $component = $__componentOriginal18e1f2087a35ad7f1de327d753761793; ?>
<?php unset($__componentOriginal18e1f2087a35ad7f1de327d753761793); ?>
<?php endif; ?>
            </div>

            <div>
                <?php if (isset($component)) { $__componentOriginal18e1f2087a35ad7f1de327d753761793 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18e1f2087a35ad7f1de327d753761793 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.searchable-select','data' => ['wire:model' => 'createPriority','label' => __('Priority'),'options' => $priorityOptions,'placeholder' => __('Select a priority'),'searchPlaceholder' => __('Search priorities…'),'emptyMessage' => __('No priorities match your search.'),'dataTest' => 'task-create-priority']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'createPriority','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Priority')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priorityOptions),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select a priority')),'searchPlaceholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search priorities…')),'emptyMessage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No priorities match your search.')),'data-test' => 'task-create-priority']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18e1f2087a35ad7f1de327d753761793)): ?>
<?php $attributes = $__attributesOriginal18e1f2087a35ad7f1de327d753761793; ?>
<?php unset($__attributesOriginal18e1f2087a35ad7f1de327d753761793); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18e1f2087a35ad7f1de327d753761793)): ?>
<?php $component = $__componentOriginal18e1f2087a35ad7f1de327d753761793; ?>
<?php unset($__componentOriginal18e1f2087a35ad7f1de327d753761793); ?>
<?php endif; ?>
            </div>

            <div>
                <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => 'createStartDate','label' => __('Start date'),'placeholder' => __('Start date'),'testId' => 'task-create-start-date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'createStartDate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start date')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start date')),'test-id' => 'task-create-start-date']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $attributes = $__attributesOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $component = $__componentOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__componentOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
            </div>

            <div>
                <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => 'createEndDate','label' => __('Due date'),'placeholder' => __('Due date'),'testId' => 'task-create-end-date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'createEndDate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Due date')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Due date')),'test-id' => 'task-create-end-date']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $attributes = $__attributesOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $component = $__componentOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__componentOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
            </div>
        </div>

        
        <div>
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
<?php echo e(__('Assignees')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->teamMembers->isEmpty()): ?>
                <p class="mt-1.5 text-sm" style="color: var(--ink-dim);"><?php echo e(__('No team members.')); ?></p>
            <?php else: ?>
                <div class="mt-1.5 flex flex-wrap gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $isSelected = in_array($member->id, $createAssigneeIds, true); ?>
                        <button
                            type="button"
                            wire:click="toggleCreateAssignee(<?php echo e($member->id); ?>)"
                            class="flex items-center gap-2 rounded-full border px-2 py-1 text-sm transition"
                            style="
                                border-color: <?php echo e($isSelected ? 'var(--blue)' : 'var(--border-strong)'); ?>;
                                background-color: <?php echo e($isSelected ? 'color-mix(in srgb, var(--blue) 15%, transparent)' : 'transparent'); ?>;
                                color: var(--ink);
                            "
                            data-test="task-create-assignee-<?php echo e($member->id); ?>"
                        >
                            <?php if (isset($component)) { $__componentOriginale3397880bba7e695d7cda0d1dcd7040f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.avatar','data' => ['name' => $member->name,'initials' => $member->initials(),'src' => $member->avatar,'size' => 'xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->initials()),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->avatar),'size' => 'xs']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3397880bba7e695d7cda0d1dcd7040f)): ?>
<?php $attributes = $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f; ?>
<?php unset($__attributesOriginale3397880bba7e695d7cda0d1dcd7040f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3397880bba7e695d7cda0d1dcd7040f)): ?>
<?php $component = $__componentOriginale3397880bba7e695d7cda0d1dcd7040f; ?>
<?php unset($__componentOriginale3397880bba7e695d7cda0d1dcd7040f); ?>
<?php endif; ?>
                            <span class="truncate"><?php echo e($member->name); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelected): ?>
                                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'check','class' => 'size-3.5 shrink-0','style' => 'color: var(--blue);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-3.5 shrink-0','style' => 'color: var(--blue);']); ?>
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal4269bfd92fa85e2de78666dd35a6befa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.textarea','data' => ['wire:model' => 'createDescription','label' => __('Description'),'rows' => '6','maxlength' => '5000','dataTest' => 'task-create-description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'createDescription','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Description')),'rows' => '6','maxlength' => '5000','data-test' => 'task-create-description']); ?>
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

        
        <div class="flex items-center justify-end gap-2 border-t pt-4" style="border-color: var(--border);">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'submit','variant' => 'primary','icon' => 'plus','dataTest' => 'task-create-submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','icon' => 'plus','data-test' => 'task-create-submit']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Create task')); ?>

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
<?php if (isset($__attributesOriginal7b48427f97b7da498a55d72398ac1330)): ?>
<?php $attributes = $__attributesOriginal7b48427f97b7da498a55d72398ac1330; ?>
<?php unset($__attributesOriginal7b48427f97b7da498a55d72398ac1330); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b48427f97b7da498a55d72398ac1330)): ?>
<?php $component = $__componentOriginal7b48427f97b7da498a55d72398ac1330; ?>
<?php unset($__componentOriginal7b48427f97b7da498a55d72398ac1330); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/task-create-drawer.blade.php ENDPATH**/ ?>