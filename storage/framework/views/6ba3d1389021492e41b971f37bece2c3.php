
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'end',
    'position' => 'bottom',
    'closeOnClickInside' => true,
    'panelClass' => '',
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
    'align' => 'end',
    'position' => 'bottom',
    'closeOnClickInside' => true,
    'panelClass' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.popover','data' => ['align' => $align,'position' => $position,'closeOnClickInside' => $closeOnClickInside,'panelClass' => $panelClass,'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($align),'position' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($position),'closeOnClickInside' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($closeOnClickInside),'panelClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($panelClass),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($trigger)): ?>
         <?php $__env->slot('trigger', null, []); ?> <?php echo e($trigger); ?> <?php $__env->endSlot(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $attributes = $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $component = $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/components/dashy/dropdown.blade.php ENDPATH**/ ?>