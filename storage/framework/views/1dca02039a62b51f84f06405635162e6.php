<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'text',
    'position' => 'top',  // top | bottom | left | right
    'align' => 'center',  // start | center | end (only used for top/bottom)
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
    'text',
    'position' => 'top',  // top | bottom | left | right
    'align' => 'center',  // start | center | end (only used for top/bottom)
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $vertical = in_array($position, ['top', 'bottom'], true);
    $horizontalAnchor = match ($align) {
        'start' => 'left-0',
        'end' => 'right-0',
        default => 'left-1/2 -translate-x-1/2',
    };
    $verticalAnchor = match ($align) {
        'start' => 'top-0',
        'end' => 'bottom-0',
        default => 'top-1/2 -translate-y-1/2',
    };

    $posClass = match ($position) {
        'bottom' => 'top-full mt-1 ' . $horizontalAnchor,
        'left' => 'right-full mr-1 ' . $verticalAnchor,
        'right' => 'left-full ml-1 ' . $verticalAnchor,
        default => 'bottom-full mb-1 ' . $horizontalAnchor,
    };
?>

<span
    x-data="{ show: false }"
    @mouseenter="show = true"
    @mouseleave="show = false"
    @focusin="show = true"
    @focusout="show = false"
    <?php echo e($attributes->class(['relative inline-flex'])); ?>

>
    <?php echo e($slot); ?>

    <span
        x-show="show"
        x-cloak
        x-transition.opacity.duration.100ms
        class="dashy-tooltip <?php echo e($posClass); ?>"
    ><?php echo e($text); ?></span>
</span>
<?php /**PATH /var/www/html/resources/views/components/dashy/tooltip.blade.php ENDPATH**/ ?>