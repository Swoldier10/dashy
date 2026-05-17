<?php
use App\Domains\Teams\Actions\ListTeamsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\CreateTeamService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
?>

<div class="mx-auto flex w-full max-w-3xl flex-col gap-10 px-6 pt-10 pb-16 sm:px-6 lg:px-8">
    <div>
        <h1 class="font-display text-3xl" style="color: var(--ink);"><?php echo e(__('Teams')); ?></h1>
        <p class="mt-2 text-sm" style="color: var(--ink-muted);">
            <?php echo e(__('Create teams, invite people, and manage your memberships.')); ?>

        </p>
        <div class="mt-6 border-t" style="border-color: var(--border);"></div>
    </div>

    
    <section class="space-y-4">
        <h2 class="font-display text-xl" style="color: var(--ink);"><?php echo e(__('Create a team')); ?></h2>
        <form wire:submit="createTeam" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'name','label' => __('Team name'),'placeholder' => __('e.g. Acme Inc.'),'required' => true,'autocomplete' => 'off','dataTest' => 'create-team-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'name','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Team name')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('e.g. Acme Inc.')),'required' => true,'autocomplete' => 'off','data-test' => 'create-team-name']); ?>
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
            </div>
            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','dataTest' => 'create-team-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','data-test' => 'create-team-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php echo e(__('Create team')); ?>

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
        </form>
    </section>

    <div class="border-t" style="border-color: var(--border);"></div>

    
    <section class="space-y-4">
        <h2 class="font-display text-xl" style="color: var(--ink);"><?php echo e(__('Your teams')); ?></h2>

        <div class="flex flex-col gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'team-row-'.e($team->id).''; ?>wire:key="team-row-<?php echo e($team->id); ?>"
                    href="<?php echo e(route('teams.show', $team)); ?>"
                    wire:navigate
                    class="flex flex-col gap-3 rounded-xl border p-4 transition sm:flex-row sm:items-center sm:gap-4"
                    style="border-color: var(--border-mid); background-color: var(--surface);"
                    onmouseover="this.style.backgroundColor='var(--surface-2)'"
                    onmouseout="this.style.backgroundColor='var(--surface)'"
                    data-test="team-row-<?php echo e($team->id); ?>"
                >
                    <?php if (isset($component)) { $__componentOriginale3397880bba7e695d7cda0d1dcd7040f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.avatar','data' => ['size' => 'sm','name' => $team->name,'initials' => $team->initials(),'src' => $team->logo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->initials()),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->logo)]); ?>
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
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-sm font-medium" style="color: var(--ink);">
                                <?php echo e($team->name); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($team->personal_team): ?>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    style="background-color: var(--surface-2); color: var(--ink-muted);"
                                >
                                    <?php echo e(__('Personal')); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="mt-1 text-xs" style="color: var(--ink-muted);">
                            <?php echo e(trans_choice('{1} 1 member|[2,*] :count members', $team->members_count, ['count' => $team->members_count])); ?>

                        </p>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs"
                            style="background-color: var(--accent); color: var(--bg-deep);"
                        >
                            <?php echo e($this->roleLabelFor($team)); ?>

                        </span>
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-right','class' => 'size-4','style' => 'color: var(--ink-dim);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'size-4','style' => 'color: var(--ink-dim);']); ?>
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
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="text-sm" style="color: var(--ink-dim);">
                    <?php echo e(__('You\'re not a member of any teams yet.')); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/8dcc9833.blade.php ENDPATH**/ ?>