<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md', // sm | md | lg | xl
    'as' => 'h2',
    'display' => null, // bool — force display font (Fraunces); auto for lg/xl
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
    'size' => 'md', // sm | md | lg | xl
    'as' => 'h2',
    'display' => null, // bool — force display font (Fraunces); auto for lg/xl
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    [$sizeClass, $defaultDisplay] = match ($size) {
        'sm' => ['text-base font-semibold', false],
        'md' => ['text-lg font-semibold', false],
        'lg' => ['text-xl', true],
        'xl' => ['text-2xl sm:text-3xl', true],
        default => ['text-lg font-semibold', false],
    };

    $useDisplay = $display ?? $defaultDisplay;
?>

<<?php echo e($as); ?>

    <?php echo e($attributes->class([
        $sizeClass,
        'font-display' => $useDisplay,
        'tracking-tight',
    ])); ?>

    style="color: var(--ink); <?php echo e($attributes->get('style')); ?>"
>
    <?php echo e($slot); ?>

</<?php echo e($as); ?>>
<?php /**PATH /var/www/html/resources/views/components/dashy/heading.blade.php ENDPATH**/ ?>