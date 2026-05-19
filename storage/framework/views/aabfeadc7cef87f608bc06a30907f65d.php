<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => '#',
    'variant' => 'default', // default | subtle | underline
    'wireNavigate' => false,
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
    'href' => '#',
    'variant' => 'default', // default | subtle | underline
    'wireNavigate' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $base = 'transition-colors';
    $variantClass = match ($variant) {
        'subtle' => 'underline underline-offset-4 decoration-[1px]',
        'underline' => 'underline underline-offset-4 decoration-[1.5px]',
        default => 'underline underline-offset-4 decoration-[1.5px]',
    };
?>

<a
    href="<?php echo e($href); ?>"
    <?php if($wireNavigate): ?> wire:navigate <?php endif; ?>
    <?php echo e($attributes->class([$base, $variantClass])); ?>

    style="color: var(--blue); <?php echo e($attributes->get('style')); ?>"
    onmouseover="this.style.color='var(--blue-soft)'"
    onmouseout="this.style.color='var(--blue)'"
>
    <?php echo e($slot); ?>

</a>
<?php /**PATH /var/www/html/resources/views/components/dashy/link.blade.php ENDPATH**/ ?>