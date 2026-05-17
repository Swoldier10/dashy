<?php
    use App\Domains\Tasks\Enums\TaskPriority;
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];
    $priorityOptions = [];
    foreach ($priorities as $priorityOption) {
        $priorityOptions[$priorityOption->value] = $priorityOption->label();
    }
    $statusOptions = $this->statuses->mapWithKeys(fn ($s) => [(string) $s->id => $s->name])->all();
    $task = $this->task;
    $colorVar = $task?->status?->category?->colorVar() ?? '--ink-dim';
    $timePanelTaskId = $task?->id ?? 0;
?>

<div>
    
    <?php if (isset($component)) { $__componentOriginal7b48427f97b7da498a55d72398ac1330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b48427f97b7da498a55d72398ac1330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.drawer','data' => ['name' => 'task-detail','side' => 'right','size' => 'lg','class' => 'md:!max-w-[820px]','focusable' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'task-detail','side' => 'right','size' => 'lg','class' => 'md:!max-w-[820px]','focusable' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'task-detail-content-'.e($taskId ?? 'empty').''; ?>wire:key="task-detail-content-<?php echo e($taskId ?? 'empty'); ?>" class="flex h-full min-h-0 flex-col">
            <form
                wire:submit.prevent
                class="flex h-full min-h-0 flex-col"
                data-test="task-detail-<?php echo e($task?->id ?? 'empty'); ?>"
            >
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task): ?>
                    <header
                        class="flex shrink-0 items-center justify-between gap-3 border-b px-6 py-5"
                        style="border-color: var(--border);"
                    >
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                                style="background-color: color-mix(in srgb, var(<?php echo e($colorVar); ?>) 18%, transparent); color: var(<?php echo e($colorVar); ?>);"
                            >
                                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'pencil-square','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil-square','class' => 'size-3']); ?>
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
                                <span><?php echo e(__('Edit task')); ?></span>
                            </span>
                            <span class="truncate text-xs" style="color: var(--ink-dim);"><?php echo e($task->project->name); ?></span>
                        </div>
                    </header>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="flex min-h-0 flex-1 flex-col gap-6 overflow-y-auto px-6 py-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task): ?>
                        
                        <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model.blur' => 'detailName','wire:change' => 'saveTaskDetail','label' => __('Name'),'placeholder' => __('What needs to be done?'),'maxlength' => '200','dataTest' => 'task-detail-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.blur' => 'detailName','wire:change' => 'saveTaskDetail','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Name')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('What needs to be done?')),'maxlength' => '200','data-test' => 'task-detail-name']); ?>
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
                            <?php if (isset($component)) { $__componentOriginal18e1f2087a35ad7f1de327d753761793 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18e1f2087a35ad7f1de327d753761793 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.searchable-select','data' => ['wire:model.live' => 'detailStatusId','label' => __('Status'),'options' => $statusOptions,'placeholder' => __('Select a status'),'searchPlaceholder' => __('Search statuses…'),'emptyMessage' => __('No statuses match your search.'),'dataTest' => 'task-detail-status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'detailStatusId','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusOptions),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select a status')),'searchPlaceholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search statuses…')),'emptyMessage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No statuses match your search.')),'data-test' => 'task-detail-status']); ?>
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

                            <?php if (isset($component)) { $__componentOriginal18e1f2087a35ad7f1de327d753761793 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18e1f2087a35ad7f1de327d753761793 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.searchable-select','data' => ['wire:model.live' => 'detailPriority','label' => __('Priority'),'options' => $priorityOptions,'placeholder' => __('Select a priority'),'searchPlaceholder' => __('Search priorities…'),'emptyMessage' => __('No priorities match your search.'),'dataTest' => 'task-detail-priority']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'detailPriority','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Priority')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priorityOptions),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select a priority')),'searchPlaceholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search priorities…')),'emptyMessage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No priorities match your search.')),'data-test' => 'task-detail-priority']); ?>
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

                            <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => 'detailStartDate','label' => __('Start date'),'placeholder' => __('Start date'),'onChange' => 'saveTaskDetail','withTime' => true,'testId' => 'task-detail-start-date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'detailStartDate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start date')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start date')),'on-change' => 'saveTaskDetail','with-time' => true,'test-id' => 'task-detail-start-date']); ?>
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

                            <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => 'detailEndDate','label' => __('Due date'),'placeholder' => __('Due date'),'onChange' => 'saveTaskDetail','withTime' => true,'testId' => 'task-detail-end-date']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'detailEndDate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Due date')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Due date')),'on-change' => 'saveTaskDetail','with-time' => true,'test-id' => 'task-detail-end-date']); ?>
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
                                <p class="text-sm" style="color: var(--ink-dim);"><?php echo e(__('No team members.')); ?></p>
                            <?php else: ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php $isSelected = $task->assignees->contains('id', $member->id); ?>
                                        <button
                                            type="button"
                                            wire:click="toggleAssignee(<?php echo e($member->id); ?>)"
                                            class="flex items-center gap-2 rounded-full border px-2 py-1 text-sm transition"
                                            style="
                                                border-color: <?php echo e($isSelected ? 'var(--blue)' : 'var(--border-strong)'); ?>;
                                                background-color: <?php echo e($isSelected ? 'color-mix(in srgb, var(--blue) 15%, transparent)' : 'transparent'); ?>;
                                                color: var(--ink);
                                            "
                                            data-test="task-detail-assignee-<?php echo e($member->id); ?>"
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.textarea','data' => ['wire:model.blur' => 'detailDescription','wire:change' => 'saveTaskDetail','label' => __('Description'),'rows' => '6','maxlength' => '5000','dataTest' => 'task-detail-description']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.blur' => 'detailDescription','wire:change' => 'saveTaskDetail','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Description')),'rows' => '6','maxlength' => '5000','data-test' => 'task-detail-description']); ?>
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

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($task->attachments)): ?>
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
<?php echo e(__('Attachments')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
                                <div class="mt-1.5 flex flex-wrap gap-2" data-test="task-detail-attachments">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $task->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($att['type'] ?? null) === 'image' && ! empty($att['url'])): ?>
                                            <a
                                                href="<?php echo e($att['url']); ?>"
                                                target="_blank"
                                                rel="noopener"
                                                class="block overflow-hidden rounded-lg border"
                                                style="border-color: var(--border-mid);"
                                            >
                                                <img
                                                    src="<?php echo e($att['url']); ?>"
                                                    alt="<?php echo e($att['name'] ?? ''); ?>"
                                                    class="block h-24 w-24 object-cover sm:h-28 sm:w-28"
                                                    loading="lazy"
                                                />
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php elseif($taskId !== null): ?>
                        <p style="color: var(--ink-muted);">
                            <?php echo e(__('This task is no longer available.')); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('time-tracking.task-time-panel', ['taskId' => $timePanelTaskId]);

$__keyOuter = $__key ?? null;

$__key = 'time-panel-'.$timePanelTaskId;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1623714718-0', $__key);

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
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task): ?>
                    <footer
                        class="flex shrink-0 items-center justify-between gap-2 border-t px-6 py-4"
                        style="border-color: var(--border);"
                    >
                        <p class="text-xs" style="color: var(--ink-dim);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->creator): ?>
                                <?php echo e(__('Created by :name', ['name' => $task->creator->name])); ?>

                                ·
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e($task->created_at?->diffForHumans()); ?>

                        </p>
                        <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'danger','icon' => 'trash','size' => 'sm','wire:click' => 'deleteTask','dataTest' => 'task-detail-delete']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'danger','icon' => 'trash','size' => 'sm','wire:click' => 'deleteTask','data-test' => 'task-detail-delete']); ?>
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
                    </footer>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
        </div>
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
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/task-detail-drawer.blade.php ENDPATH**/ ?>