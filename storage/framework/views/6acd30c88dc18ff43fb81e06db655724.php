<div class="flex flex-col gap-4" data-test="project-dashboard-panel">
    <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
        <?php if (isset($component)) { $__componentOriginal5f13bf0e70ca48a0203bb58f364b7634 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5f13bf0e70ca48a0203bb58f364b7634 = $attributes; } ?>
<?php $component = App\View\Components\Dashy\Tabs::resolve(['wireModel' => 'scope','defaultValue' => ''.e($scope).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tab','data' => ['value' => 'me']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'me']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Me')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.tab','data' => ['value' => 'team']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'team']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Team')); ?> <?php echo $__env->renderComponent(); ?>
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

        <div class="flex items-center justify-center gap-2 sm:justify-end">
            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'previousMonth','variant' => 'ghost','size' => 'sm','icon' => 'chevron-left','ariaLabel' => ''.e(__('Previous month')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previousMonth','variant' => 'ghost','size' => 'sm','icon' => 'chevron-left','aria-label' => ''.e(__('Previous month')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <span class="sr-only"><?php echo e(__('Previous month')); ?></span>
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

            <span
                class="min-w-[8rem] text-center font-display text-sm"
                style="color: var(--ink);"
                data-test="project-dashboard-month-label"
            ><?php echo e($this->monthLabel); ?></span>

            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'nextMonth','variant' => 'ghost','size' => 'sm','icon' => 'chevron-right','ariaLabel' => ''.e(__('Next month')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'nextMonth','variant' => 'ghost','size' => 'sm','icon' => 'chevron-right','aria-label' => ''.e(__('Next month')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <span class="sr-only"><?php echo e(__('Next month')); ?></span>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($this->isCurrentMonth)): ?>
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'goToCurrentMonth','variant' => 'ghost','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'goToCurrentMonth','variant' => 'ghost','size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Today')); ?>

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

            <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['wire:click' => 'exportMonth','wire:loading.attr' => 'disabled','wire:target' => 'exportMonth','variant' => 'ghost','size' => 'sm','icon' => 'document-arrow-down','ariaLabel' => ''.e(__('Excel-Export für ausgewählten Monat')).'','dataTest' => 'project-dashboard-export']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'exportMonth','wire:loading.attr' => 'disabled','wire:target' => 'exportMonth','variant' => 'ghost','size' => 'sm','icon' => 'document-arrow-down','aria-label' => ''.e(__('Excel-Export für ausgewählten Monat')).'','data-test' => 'project-dashboard-export']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <span class="hidden md:inline"><?php echo e(__('Excel')); ?></span>
                <span class="sr-only md:hidden"><?php echo e(__('Excel-Export für ausgewählten Monat')); ?></span>
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

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md','class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md','class' => 'lg:col-span-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="flex flex-col gap-2">
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
<?php echo e(__('Total time')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
                <span
                    class="font-display text-3xl sm:text-4xl"
                    style="color: var(--ink);"
                    data-test="project-dashboard-total-month"
                >
                    <?php echo e(\App\Domains\TimeTracking\Support\DurationParser::format($this->totalMonthSeconds)); ?>

                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->totalMonthMoney !== null): ?>
                    <span
                        class="font-display text-base sm:text-lg"
                        style="color: var(--ink-muted);"
                        data-test="project-dashboard-total-money"
                    >
                        <?php echo e($this->totalMonthMoney); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58529e7c0f4c9916863fa3c6eb38c7f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.card','data' => ['padding' => 'md','class' => 'lg:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'md','class' => 'lg:col-span-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="flex flex-col gap-3">
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
<?php echo e(__('Daily hours')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
                <div wire:ignore class="relative h-64 w-full">
                    <canvas
                        x-data="dashyHoursChart(<?php echo \Illuminate\Support\Js::from($this->chartLabels)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($this->chartData)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($this->chartCounts)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($this->billingRate)->toHtml() ?>)"
                        @chart-data-updated.window="update($event.detail.labels, $event.detail.values, $event.detail.counts)"
                        data-test="project-dashboard-chart"
                    ></canvas>
                </div>
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
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/projects/project-dashboard-panel.blade.php ENDPATH**/ ?>