<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title',
    'description',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex w-full flex-col text-center">
    <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($title); ?> <?php echo $__env->renderComponent(); ?>
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
<?php echo e($description); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale626700ad092668e460de4abfec60854)): ?>
<?php $attributes = $__attributesOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__attributesOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale626700ad092668e460de4abfec60854)): ?>
<?php $component = $__componentOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__componentOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/components/auth-header.blade.php ENDPATH**/ ?>