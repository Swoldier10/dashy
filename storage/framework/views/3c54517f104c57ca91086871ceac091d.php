<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default', // default | subtle
    'color' => null,        // null | error | success | warning | muted
    'size' => 'sm',         // xs | sm | md
    'as' => 'p',
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
    'color' => null,        // null | error | success | warning | muted
    'size' => 'sm',         // xs | sm | md
    'as' => 'p',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizeClass = match ($size) {
        'xs' => 'text-xs',
        'md' => 'text-base',
        default => 'text-sm',
    };

    $colorStyle = match ($color) {
        'error' => 'color: var(--state-error);',
        'success' => 'color: var(--state-success);',
        'warning' => 'color: var(--state-warning);',
        'muted', 'subtle' => 'color: var(--ink-muted);',
        default => $variant === 'subtle' ? 'color: var(--ink-muted);' : 'color: var(--ink);',
    };
?>

<<?php echo e($as); ?>

    <?php echo e($attributes->class(['leading-relaxed', $sizeClass])); ?>

    style="<?php echo e($colorStyle); ?> <?php echo e($attributes->get('style')); ?>"
>
    <?php echo e($slot); ?>

</<?php echo e($as); ?>>
<?php /**PATH /var/www/html/resources/views/components/dashy/text.blade.php ENDPATH**/ ?>