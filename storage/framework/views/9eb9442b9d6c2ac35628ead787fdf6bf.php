<?php
use Livewire\Component;
?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3><?php echo e(__('Appearance')); ?></h3>
            <p><?php echo e(__('How Dashy looks on this device.')); ?></p>
        </div>

        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label">
                <span class="row-label-text"><?php echo e(__('Theme')); ?></span>
                <span class="row-label-desc"><?php echo e(__('Light, dark, or follow the system setting.')); ?></span>
            </div>
            <div class="dashy-settings-row-value">
                <?php if (isset($component)) { $__componentOriginald643b91a9e501d5fbe5fbabe32bbe1bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald643b91a9e501d5fbe5fbabe32bbe1bb = $attributes; } ?>
<?php $component = App\View\Components\Dashy\RadioGroup::resolve(['name' => 'appearance','variant' => 'segmented'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.radio-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Dashy\RadioGroup::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-data' => '{
                        value: localStorage.getItem(\'appearance\') ?? \'system\',
                        apply(v) {
                            localStorage.setItem(\'appearance\', v);
                            const dark = v === \'dark\' || (v === \'system\' && window.matchMedia(\'(prefers-color-scheme: dark)\').matches);
                            document.documentElement.classList.toggle(\'dark\', dark);
                        },
                    }','x-init' => 'apply(value)','@change' => 'apply(value)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal6dad574148e65d4ed48604b495021708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6dad574148e65d4ed48604b495021708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.radio','data' => ['value' => 'light','icon' => 'sun','xModel' => 'value']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.radio'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'light','icon' => 'sun','x-model' => 'value']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Light')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $attributes = $__attributesOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__attributesOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $component = $__componentOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__componentOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal6dad574148e65d4ed48604b495021708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6dad574148e65d4ed48604b495021708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.radio','data' => ['value' => 'dark','icon' => 'moon','xModel' => 'value']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.radio'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'dark','icon' => 'moon','x-model' => 'value']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Dark')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $attributes = $__attributesOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__attributesOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $component = $__componentOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__componentOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal6dad574148e65d4ed48604b495021708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6dad574148e65d4ed48604b495021708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.radio','data' => ['value' => 'system','icon' => 'computer-desktop','xModel' => 'value']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.radio'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'system','icon' => 'computer-desktop','x-model' => 'value']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('System')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $attributes = $__attributesOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__attributesOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6dad574148e65d4ed48604b495021708)): ?>
<?php $component = $__componentOriginal6dad574148e65d4ed48604b495021708; ?>
<?php unset($__componentOriginal6dad574148e65d4ed48604b495021708); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald643b91a9e501d5fbe5fbabe32bbe1bb)): ?>
<?php $attributes = $__attributesOriginald643b91a9e501d5fbe5fbabe32bbe1bb; ?>
<?php unset($__attributesOriginald643b91a9e501d5fbe5fbabe32bbe1bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald643b91a9e501d5fbe5fbabe32bbe1bb)): ?>
<?php $component = $__componentOriginald643b91a9e501d5fbe5fbabe32bbe1bb; ?>
<?php unset($__componentOriginald643b91a9e501d5fbe5fbabe32bbe1bb); ?>
<?php endif; ?>
            </div>
        </div>
    </section>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/c1630d02.blade.php ENDPATH**/ ?>