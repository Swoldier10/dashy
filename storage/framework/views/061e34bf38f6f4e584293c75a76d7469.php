<?php
use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\ListAllTasksForUserService;
use App\Domains\Tasks\Services\ListTasksForProjectService;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Tasks\Services\ReorderTasksService;
use App\Domains\Tasks\Services\ToggleTaskCompleteService;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
?>

<div class="flex min-h-0 flex-1 flex-col" data-test="tasks-page">
    <?php echo $__env->make('livewire.tasks.partials.page-top-bar', [
        'teams' => $this->teams,
        'teamCounts' => $this->teamCounts,
        'totalCount' => $this->allTasksAcrossTeams->count(),
        'activeTeamId' => $this->fromContext === 'everything' ? null : $this->project->team_id,
        'everythingHref' => route('tasks'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mx-auto flex w-full max-w-screen-2xl flex-1 min-h-0 flex-col gap-4 overflow-y-auto px-4 py-4 lg:flex-row lg:gap-6 lg:overflow-hidden lg:px-6 lg:py-0">
        <?php echo $__env->make('livewire.tasks.partials.workspace-sidebar', [
            'teams' => $this->teams,
            'projectsByTeamId' => $this->projectsByTeamId,
            'projectTaskCounts' => $this->projectTaskCounts,
            'totalCount' => $this->allTasksAcrossTeams->count(),
            'activeProjectId' => $this->project->id,
            'isEverythingActive' => false,
            'activeTeam' => $this->fromContext === 'everything' ? null : $this->project->team,
            'activeTeamCount' => (int) ($this->teamCounts[$this->project->team_id] ?? 0),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex min-w-0 flex-1 flex-col gap-4 lg:h-full lg:overflow-y-auto lg:py-4">
            <?php echo $__env->make('livewire.tasks.partials.page-heading', [
                'breadcrumb' => [
                    ['label' => __('Workspace'), 'href' => route('tasks')],
                    ['label' => $this->project->team->name, 'href' => route('tasks', ['team' => $this->project->team_id])],
                    ['label' => $this->project->name, 'href' => null],
                ],
                'project' => $this->project,
                'title' => $this->project->name,
                'subtitle' => $this->project->description ?: __('Tasks in :name', ['name' => $this->project->name]),
                'showArchived' => $this->showArchived,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="flex items-center" data-test="tasks-action-bar">
                <?php if (isset($component)) { $__componentOriginal5f13bf0e70ca48a0203bb58f364b7634 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5f13bf0e70ca48a0203bb58f364b7634 = $attributes; } ?>
<?php $component = App\View\Components\Dashy\Tabs::resolve(['wireModel' => 'activeTab','defaultValue' => ''.e($activeTab).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Dashy\Tabs::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginald501d16b64f8bbd19ee5be7a86450fc3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tab','data' => ['value' => 'tasks']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'tasks']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Tasks')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3)): ?>
<?php $attributes = $__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3; ?>
<?php unset($__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald501d16b64f8bbd19ee5be7a86450fc3)): ?>
<?php $component = $__componentOriginald501d16b64f8bbd19ee5be7a86450fc3; ?>
<?php unset($__componentOriginald501d16b64f8bbd19ee5be7a86450fc3); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginald501d16b64f8bbd19ee5be7a86450fc3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tab','data' => ['value' => 'dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'dashboard']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Dashboard')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3)): ?>
<?php $attributes = $__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3; ?>
<?php unset($__attributesOriginald501d16b64f8bbd19ee5be7a86450fc3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald501d16b64f8bbd19ee5be7a86450fc3)): ?>
<?php $component = $__componentOriginald501d16b64f8bbd19ee5be7a86450fc3; ?>
<?php unset($__componentOriginald501d16b64f8bbd19ee5be7a86450fc3); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5f13bf0e70ca48a0203bb58f364b7634)): ?>
<?php $attributes = $__attributesOriginal5f13bf0e70ca48a0203bb58f364b7634; ?>
<?php unset($__attributesOriginal5f13bf0e70ca48a0203bb58f364b7634); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5f13bf0e70ca48a0203bb58f364b7634)): ?>
<?php $component = $__componentOriginal5f13bf0e70ca48a0203bb58f364b7634; ?>
<?php unset($__componentOriginal5f13bf0e70ca48a0203bb58f364b7634); ?>
<?php endif; ?>
            </div>

            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex flex-col gap-3',
                'hidden' => $activeTab !== 'tasks',
            ]); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php echo $__env->make('livewire.tasks.partials.status-group', [
                        'status' => $status,
                        'tasks' => $this->tasksByStatusId[$status->id] ?? collect(),
                        'isCollapsed' => in_array($status->id, $this->collapsedStatusIds, true),
                        'projectId' => $this->project->id,
                        'teamMembers' => $this->teamMembers,
                        'allStatuses' => $this->statuses,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php echo $__env->make('livewire.tasks.partials.no-statuses-empty-state', ['project' => $this->project], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex-1',
                'hidden' => $activeTab !== 'dashboard',
            ]); ?>">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('projects.project-dashboard-panel', ['project-id' => $this->project->id,'lazy' => true]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1804746835-0', $__key);

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

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1804746835-1', $__key);

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
    <?php echo $__env->make('livewire.tasks.partials.task-create-drawer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/7eb3d07f.blade.php ENDPATH**/ ?>