<?php
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Actions\ListProjectStatusesForProjectAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Projects\Services\ListProjectStatusesForUserService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\ListAllTasksForUserService;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Tasks\Services\ReorderTasksService;
use App\Domains\Tasks\Services\ToggleTaskCompleteService;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
?>

<div class="flex min-h-0 flex-1 flex-col" data-test="tasks-index-page">
    <?php echo $__env->make('livewire.tasks.partials.page-top-bar', [
        'teams' => $this->teams,
        'teamCounts' => $this->teamCounts,
        'totalCount' => $this->allTasksAcrossTeams->count(),
        'activeTeamId' => $this->teamId,
        'everythingHref' => route('tasks'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mx-auto flex w-full max-w-screen-2xl flex-1 min-h-0 flex-col gap-4 overflow-y-auto px-4 py-4 lg:flex-row lg:gap-6 lg:overflow-hidden lg:px-6 lg:py-0">
        <?php echo $__env->make('livewire.tasks.partials.workspace-sidebar', [
            'teams' => $this->teams,
            'projectsByTeamId' => $this->projectsByTeamId,
            'projectTaskCounts' => $this->projectTaskCounts,
            'totalCount' => $this->allTasksAcrossTeams->count(),
            'activeProjectId' => null,
            'isEverythingActive' => true,
            'activeTeam' => $this->activeTeam,
            'activeTeamCount' => $this->activeTeam
                ? (int) ($this->teamCounts[$this->activeTeam->id] ?? 0)
                : 0,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex min-w-0 flex-1 flex-col gap-4 lg:h-full lg:overflow-y-auto lg:py-4">
            <?php echo $__env->make('livewire.tasks.partials.page-heading', [
                'breadcrumb' => [
                    ['label' => __('Workspace'), 'href' => route('tasks')],
                    [
                        'label' => $this->activeTeam ? $this->activeTeam->name : __('Everything'),
                        'href' => null,
                    ],
                ],
                'project' => null,
                'title' => $this->activeTeam ? $this->activeTeam->name : __('All tasks'),
                'showArchived' => $this->showArchived,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex flex-col gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->statusBuckets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bucket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $isCollapsed = in_array($bucket['key'], $this->collapsedStatusKeys, true);
                        $bucketHasTasks = count($bucket['tasks']) > 0;
                    ?>
                    <section id="<?php echo e($bucket['anchor']); ?>"
                             class="flex flex-col gap-2"
                             <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'bucket-'.e($bucket['key']).''; ?>wire:key="bucket-<?php echo e($bucket['key']); ?>"
                             data-test="aggregator-bucket-<?php echo e($bucket['key']); ?>">
                        <header class="flex items-center justify-between gap-2 px-1 py-1">
                            <button
                                type="button"
                                wire:click="toggleStatusCollapseByKey('<?php echo e($bucket['key']); ?>')"
                                class="flex items-center gap-2 rounded-md px-1 py-1 transition focus:outline-none focus-visible:ring-2"
                                style="--tw-ring-color: var(--blue);"
                                aria-expanded="<?php echo e($isCollapsed ? 'false' : 'true'); ?>"
                            >
                                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $isCollapsed ? 'chevron-right' : 'chevron-down','class' => 'size-3.5 shrink-0','style' => 'color: var(--ink-dim);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isCollapsed ? 'chevron-right' : 'chevron-down'),'class' => 'size-3.5 shrink-0','style' => 'color: var(--ink-dim);']); ?>
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
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                                    style="background-color: color-mix(in srgb, var(<?php echo e($bucket['colorVar']); ?>) 14%, transparent); color: color-mix(in srgb, var(<?php echo e($bucket['colorVar']); ?>) 80%, var(--ink));"
                                >
                                    <span class="inline-block size-1.5 rounded-full" style="background-color: var(<?php echo e($bucket['colorVar']); ?>);"></span>
                                    <span><?php echo e($bucket['label']); ?></span>
                                </span>
                                <span class="text-xs" style="color: var(--ink-dim);"><?php echo e($bucket['count']); ?></span>
                            </button>

                            <button
                                type="button"
                                wire:click="openCreateTask"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs transition"
                                style="color: var(--ink-dim);"
                                onmouseover="this.style.color='var(--blue)'; this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)';"
                                onmouseout="this.style.color='var(--ink-dim)'; this.style.backgroundColor='transparent';"
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
                                <span><?php echo e(__('Add')); ?></span>
                            </button>
                        </header>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isCollapsed): ?>
                            <div
                                x-data
                                x-init="
                                    if (window.Sortable) {
                                        new window.Sortable($el, {
                                            animation: 150,
                                            group: 'aggregator-tasks',
                                            handle: '.task-drag-handle',
                                            draggable: '[data-task-id]',
                                            onEnd: (evt) => {
                                                const taskId = parseInt(evt.item.dataset.taskId, 10);
                                                const fromKey = evt.from.dataset.bucketKey;
                                                const toKey = evt.to.dataset.bucketKey;
                                                const sourceIds = [...evt.from.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                                                const targetIds = [...evt.to.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                                                if (fromKey === toKey) {
                                                    $wire.aggregatorReorderBucket(taskId, targetIds);
                                                } else {
                                                    $wire.aggregatorMoveBetweenBuckets(taskId, toKey, sourceIds, targetIds);
                                                }
                                            },
                                        });
                                    }
                                "
                                wire:ignore.self
                                data-bucket-key="<?php echo e($bucket['key']); ?>"
                                data-test="aggregator-sortable-<?php echo e($bucket['key']); ?>"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'flex flex-col overflow-hidden',
                                    'rounded-xl' => $bucketHasTasks,
                                ]); ?>"
                                style="
                                    min-height: <?php echo e($bucketHasTasks ? '0' : '8px'); ?>;
                                    background-color: <?php echo e($bucketHasTasks ? 'var(--surface)' : 'transparent'); ?>;
                                    box-shadow: <?php echo e($bucketHasTasks
                                        ? '0 1px 2px rgba(var(--ink-rgb), 0.04), 0 0 0 1px var(--border) inset'
                                        : 'none'); ?>;
                                "
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bucket['tasks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php echo $__env->make('livewire.tasks.partials.task-row-card', [
                                        'task' => $task,
                                        'teamMembers' => $task->project->team->members,
                                        'allStatuses' => $task->project->statuses,
                                        'showProjectPill' => true,
                                        'showStatusPill' => false,
                                        'showCheckbox' => false,
                                        'showDragHandle' => true,
                                        'plainMeta' => true,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="flex flex-col items-center justify-center gap-3 py-12 text-center" data-test="tasks-index-empty">
                        <div class="flex size-12 items-center justify-center rounded-xl"
                             style="background-color: var(--surface-2); color: var(--ink-dim);">
                            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'clipboard-document-check','class' => 'size-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard-document-check','class' => 'size-6']); ?>
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
                        </div>
                        <p class="text-sm" style="color: var(--ink-muted);">
                            <?php echo e(__('No tasks yet. Create your first task to see it here.')); ?>

                        </p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tasks.task-detail-drawer', ['taskId' => $initialTaskId]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2934145933-0', $__key);

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
    <?php echo $__env->make('livewire.tasks.partials.task-create-drawer-aggregator', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/2ba0a1eb.blade.php ENDPATH**/ ?>