<?php if (isset($component)) { $__componentOriginalce62a25a36fae5442b817eaef5e3dbfd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce62a25a36fae5442b817eaef5e3dbfd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app.shell','data' => ['title' => $title ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title ?? null)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce62a25a36fae5442b817eaef5e3dbfd)): ?>
<?php $attributes = $__attributesOriginalce62a25a36fae5442b817eaef5e3dbfd; ?>
<?php unset($__attributesOriginalce62a25a36fae5442b817eaef5e3dbfd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce62a25a36fae5442b817eaef5e3dbfd)): ?>
<?php $component = $__componentOriginalce62a25a36fae5442b817eaef5e3dbfd; ?>
<?php unset($__componentOriginalce62a25a36fae5442b817eaef5e3dbfd); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>