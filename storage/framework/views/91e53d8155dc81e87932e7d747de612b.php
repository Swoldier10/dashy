<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default', // default | subtle
    'orientation' => 'horizontal', // horizontal | vertical
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
    'variant' => 'default', // default | subtle
    'orientation' => 'horizontal', // horizontal | vertical
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $color = $variant === 'subtle' ? 'var(--border)' : 'var(--border-mid)';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orientation === 'vertical'): ?>
    <span
        role="separator"
        aria-orientation="vertical"
        <?php echo e($attributes->class(['inline-block w-px self-stretch'])); ?>

        style="background-color: <?php echo e($color); ?>;"
    ></span>
<?php else: ?>
    <hr
        <?php echo e($attributes->class(['border-0 h-px w-full'])); ?>

        style="background-color: <?php echo e($color); ?>;"
    />
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/components/dashy/separator.blade.php ENDPATH**/ ?>