<?php
use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\AddTeamMemberService;
use App\Domains\Teams\Services\DeleteTeamService;
use App\Domains\Teams\Services\RemoveTeamMemberService;
use App\Domains\Teams\Services\RenameTeamService;
use App\Domains\Teams\Services\TeamLogoService;
use App\Domains\Teams\Services\UpdateTeamRateService;
use App\Models\User;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
?>

<?php
    $team = $this->team;
    $isOwner = $this->isOwner;
?>

<div class="mx-auto flex w-full max-w-3xl flex-col gap-6 px-6 pt-10 pb-16 sm:px-6 lg:px-8">
    
    <div>
        <a
            href="<?php echo e(route('teams.index')); ?>"
            wire:navigate
            class="inline-flex items-center gap-1 text-xs"
            style="color: var(--ink-dim);"
        >
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-left','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'size-3']); ?>
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
            <?php echo e(__('All teams')); ?>

        </a>
        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
            <?php if (isset($component)) { $__componentOriginale3397880bba7e695d7cda0d1dcd7040f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.avatar','data' => ['size' => 'lg','name' => $team->name,'initials' => $team->initials(),'src' => $team->logo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->initials()),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->logo)]); ?>
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
                    <h1 class="truncate font-display text-3xl" style="color: var(--ink);">
                        <?php echo e($team->name); ?>

                    </h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($team->personal_team): ?>
                        <span
                            class="rounded-full px-2 py-0.5 text-xs"
                            style="background-color: var(--surface-2); color: var(--ink-muted);"
                        >
                            <?php echo e(__('Personal')); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($team->members->count() === 1 && $isOwner && ! $team->personal_team): ?>
        <p class="text-sm" style="color: var(--ink-muted);">
            <?php echo e(__('You\'re the only member. Add someone to collaborate, or delete the team if you no longer need it.')); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner): ?>
        <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.section-heading','data' => ['title' => __('Settings'),'description' => __('Logo and name shown across Dashy.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Settings')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Logo and name shown across Dashy.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $attributes = $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $component = $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>

            
            <div class="mt-5 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-6">
                <?php if (isset($component)) { $__componentOriginale3397880bba7e695d7cda0d1dcd7040f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.avatar','data' => ['size' => 'lg','name' => $team->name,'initials' => $team->initials(),'src' => $team->logo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->initials()),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($team->logo)]); ?>
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
                <div class="flex flex-wrap gap-3">
                    <input
                        type="file"
                        wire:model="newLogo"
                        id="team-logo-input"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/webp"
                    />
                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled','xOn:click' => 'document.getElementById(\'team-logo-input\').click()','dataTest' => 'upload-logo-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled','x-on:click' => 'document.getElementById(\'team-logo-input\').click()','data-test' => 'upload-logo-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Upload new')); ?>

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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($team->logo): ?>
                        <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'ghost','wire:click' => 'removeLogo','dataTest' => 'remove-logo-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','wire:click' => 'removeLogo','data-test' => 'remove-logo-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <?php echo e(__('Remove')); ?>

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
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <?php if (isset($component)) { $__componentOriginald9fee6ad637afd79ed43645955ec1b38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9fee6ad637afd79ed43645955ec1b38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.text','data' => ['class' => 'mt-3','style' => 'color: var(--state-error);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','style' => 'color: var(--state-error);']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($message); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $attributes = $__attributesOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $component = $__componentOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__componentOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newLogo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <?php if (isset($component)) { $__componentOriginald9fee6ad637afd79ed43645955ec1b38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9fee6ad637afd79ed43645955ec1b38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.text','data' => ['class' => 'mt-3','style' => 'color: var(--state-error);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','style' => 'color: var(--state-error);']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($message); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $attributes = $__attributesOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $component = $__componentOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__componentOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-5 border-t" style="border-color: var(--border);"></div>

            
            <form wire:submit="rename" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'newName','label' => __('Name'),'required' => true,'autocomplete' => 'off','dataTest' => 'rename-team-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'newName','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Name')),'required' => true,'autocomplete' => 'off','data-test' => 'rename-team-name']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','dataTest' => 'rename-team-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','data-test' => 'rename-team-button']); ?>
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
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $attributes = $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $component = $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner): ?>
        <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.section-heading','data' => ['title' => __('Billing'),'description' => __('Default hourly rate billed for this team\'s work.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Billing')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Default hourly rate billed for this team\'s work.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $attributes = $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $component = $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>
            <form wire:submit="updateRate" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'hourlyRate','errorKey' => 'hourly_rate','label' => __('Rate'),'type' => 'number','step' => '0.01','min' => '0','inputmode' => 'decimal','autocomplete' => 'off','dataTest' => 'team-rate-input']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'hourlyRate','errorKey' => 'hourly_rate','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Rate')),'type' => 'number','step' => '0.01','min' => '0','inputmode' => 'decimal','autocomplete' => 'off','data-test' => 'team-rate-input']); ?>
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
                <div class="flex-1">
                    <?php if (isset($component)) { $__componentOriginalcafb2873943de6a347bbd054e3da5f1f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcafb2873943de6a347bbd054e3da5f1f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.select','data' => ['wire:model' => 'currency','label' => __('Currency'),'dataTest' => 'team-rate-currency']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'currency','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Currency')),'data-test' => 'team-rate-currency']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <option value=""><?php echo e(__('Select…')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = Currency::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($option->value); ?>"><?php echo e($option->label()); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcafb2873943de6a347bbd054e3da5f1f)): ?>
<?php $attributes = $__attributesOriginalcafb2873943de6a347bbd054e3da5f1f; ?>
<?php unset($__attributesOriginalcafb2873943de6a347bbd054e3da5f1f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcafb2873943de6a347bbd054e3da5f1f)): ?>
<?php $component = $__componentOriginalcafb2873943de6a347bbd054e3da5f1f; ?>
<?php unset($__componentOriginalcafb2873943de6a347bbd054e3da5f1f); ?>
<?php endif; ?>
                </div>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','dataTest' => 'update-rate-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','data-test' => 'update-rate-button']); ?>
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
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $attributes = $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $component = $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.section-heading','data' => ['title' => __('Members'),'description' => trans_choice('{1} :count person has access to this team.|[2,*] :count people have access to this team.', $team->members->count(), ['count' => $team->members->count()])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Members')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans_choice('{1} :count person has access to this team.|[2,*] :count people have access to this team.', $team->members->count(), ['count' => $team->members->count()]))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $attributes = $__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__attributesOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8)): ?>
<?php $component = $__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8; ?>
<?php unset($__componentOriginalfa6cc4a7ffd10e3af2261cae48afe2c8); ?>
<?php endif; ?>

        <div class="mt-5 flex flex-col gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $team->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $memberRole = $member->pivot->role ?? null;
                    $memberRoleEnum = $memberRole instanceof TeamRole
                        ? $memberRole
                        : ($memberRole !== null ? TeamRole::from($memberRole) : null);
                    $isSelf = $member->is(auth()->user());
                    $isMemberOwner = $memberRoleEnum === TeamRole::Owner;
                    $teamOwnersCount = $team->members
                        ->filter(function ($m) {
                            $r = $m->pivot->role ?? null;
                            $v = $r instanceof TeamRole ? $r->value : $r;
                            return $v === TeamRole::Owner->value;
                        })
                        ->count();
                ?>
                <div
                    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'member-'.e($member->id).''; ?>wire:key="member-<?php echo e($member->id); ?>"
                    class="flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between"
                    style="border-color: var(--border-mid); background-color: var(--surface);"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <?php if (isset($component)) { $__componentOriginale3397880bba7e695d7cda0d1dcd7040f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3397880bba7e695d7cda0d1dcd7040f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.avatar','data' => ['size' => 'sm','name' => $member->name,'initials' => $member->initials(),'src' => $member->avatar]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->initials()),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->avatar)]); ?>
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
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium" style="color: var(--ink);">
                                <?php echo e($member->name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelf): ?>
                                    <span class="text-xs font-normal" style="color: var(--ink-dim);"><?php echo e(__('(you)')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                            <p class="truncate text-xs" style="color: var(--ink-muted);"><?php echo e($member->email); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs"
                            style="background-color: <?php echo e($isMemberOwner ? 'var(--accent)' : 'var(--surface-2)'); ?>; color: <?php echo e($isMemberOwner ? 'var(--bg-deep)' : 'var(--ink-muted)'); ?>;"
                        >
                            <?php echo e($this->roleLabel($memberRoleEnum)); ?>

                        </span>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner && ! $isSelf && (! $isMemberOwner || $teamOwnersCount > 1)): ?>
                            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','wire:click' => 'confirmRemoveMember('.e($member->id).')','dataTest' => 'remove-member-'.e($member->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','wire:click' => 'confirmRemoveMember('.e($member->id).')','data-test' => 'remove-member-'.e($member->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php echo e(__('Remove')); ?>

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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelf && ! $team->personal_team && ! $this->isLastOwner): ?>
                            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','wire:click' => 'confirmRemoveMember('.e($member->id).')','dataTest' => 'leave-team-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','wire:click' => 'confirmRemoveMember('.e($member->id).')','data-test' => 'leave-team-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php echo e(__('Leave')); ?>

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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner): ?>
            <form wire:submit="addMember" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model' => 'inviteEmail','type' => 'email','label' => __('Add member by email'),'placeholder' => __('person@example.com'),'autocomplete' => 'off','dataTest' => 'add-member-email']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'inviteEmail','type' => 'email','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add member by email')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('person@example.com')),'autocomplete' => 'off','data-test' => 'add-member-email']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','dataTest' => 'add-member-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit','class' => 'w-full sm:w-auto','data-test' => 'add-member-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Add member')); ?>

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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $attributes = $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $component = $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner && ! $team->personal_team): ?>
        <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="dashy-section-heading">
                <div class="min-w-0 flex-1">
                    <h2 class="dashy-section-heading-title" style="color: var(--state-error);"><?php echo e(__('Danger zone')); ?></h2>
                    <p class="dashy-section-heading-description">
                        <?php echo e(__('Deleting this team removes it for everyone and cannot be undone.')); ?>

                    </p>
                </div>
            </div>
            <div class="mt-5">
                <?php if (isset($component)) { $__componentOriginal20aff6ab24f1ce19fa43d57f2b8047ce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20aff6ab24f1ce19fa43d57f2b8047ce = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal.trigger','data' => ['name' => 'confirm-delete-team']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-delete-team']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'danger','dataTest' => 'delete-team-button']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','data-test' => 'delete-team-button']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Delete team')); ?>

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
<?php if (isset($__attributesOriginal20aff6ab24f1ce19fa43d57f2b8047ce)): ?>
<?php $attributes = $__attributesOriginal20aff6ab24f1ce19fa43d57f2b8047ce; ?>
<?php unset($__attributesOriginal20aff6ab24f1ce19fa43d57f2b8047ce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20aff6ab24f1ce19fa43d57f2b8047ce)): ?>
<?php $component = $__componentOriginal20aff6ab24f1ce19fa43d57f2b8047ce; ?>
<?php unset($__componentOriginal20aff6ab24f1ce19fa43d57f2b8047ce); ?>
<?php endif; ?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $attributes = $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8)): ?>
<?php $component = $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8; ?>
<?php unset($__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'confirm-remove-member','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelRemoveMember']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-remove-member','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancelRemoveMember']); ?>
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
<?php echo e(__('Remove this member?')); ?> <?php echo $__env->renderComponent(); ?>
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

                <?php echo e(__('They will lose access to the team immediately.')); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled','wire:click' => 'cancelRemoveMember']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled','wire:click' => 'cancelRemoveMember']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'danger','wire:click' => 'removeMember','dataTest' => 'confirm-remove-member']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','wire:click' => 'removeMember','data-test' => 'confirm-remove-member']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Remove')); ?>

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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOwner && ! $team->personal_team): ?>
        <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'confirm-delete-team','focusable' => true,'class' => 'max-w-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-delete-team','focusable' => true,'class' => 'max-w-md']); ?>
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
<?php echo e(__('Delete this team?')); ?> <?php echo $__env->renderComponent(); ?>
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

                    <?php echo e(__('Every member will lose access. This cannot be undone.')); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['variant' => 'danger','wire:click' => 'deleteTeam','dataTest' => 'confirm-delete-team']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','wire:click' => 'deleteTeam','data-test' => 'confirm-delete-team']); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/2c113172.blade.php ENDPATH**/ ?>