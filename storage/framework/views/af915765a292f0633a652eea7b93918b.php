<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'label' => null,
    'description' => null,
    'rows' => 3,
    'errorKey' => null,
    'showError' => true,
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
    'name' => null,
    'label' => null,
    'description' => null,
    'rows' => 3,
    'errorKey' => null,
    'showError' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $id = $attributes->get('id') ?? ($name ? 'dashy-textarea-' . str_replace(['.', '['], ['-', '-'], (string) $name) : null);
?>

<div class="grid gap-1.5">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginal92099487053ef6086efd6f50c4bedaee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92099487053ef6086efd6f50c4bedaee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.label','data' => ['for' => $id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($id)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
        <p class="dashy-help" style="margin-top:-2px;"><?php echo e($description); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <textarea
        <?php if($id): ?> id="<?php echo e($id); ?>" <?php endif; ?>
        <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        rows="<?php echo e($rows); ?>"
        <?php echo e($attributes->merge(['class' => 'dashy-input'])); ?>

    ><?php echo e($slot); ?></textarea>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showError && $errorBagKey): ?>
        <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => $errorBagKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errorBagKey)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/textarea.blade.php ENDPATH**/ ?>