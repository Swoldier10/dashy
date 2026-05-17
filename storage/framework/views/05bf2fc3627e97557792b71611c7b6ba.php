<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'initials' => null,
    'src' => null,
    'size' => 'md', // xs | sm | md | lg
    'online' => false,
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
    'initials' => null,
    'src' => null,
    'size' => 'md', // xs | sm | md | lg
    'online' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if (! $initials && $name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : '';
        $initials = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
    }
?>

<span
    <?php echo e($attributes->class([
        'dashy-avatar',
        'dashy-avatar--' . $size,
        'relative',
    ])); ?>

    role="img"
    aria-label="<?php echo e($name); ?>"
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($src): ?>
        <img src="<?php echo e($src); ?>" alt="<?php echo e($name); ?>" />
    <?php elseif($initials): ?>
        <span aria-hidden="true"><?php echo e($initials); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($online): ?>
        <span
            aria-hidden="true"
            class="absolute -right-0.5 -bottom-0.5 size-2.5 rounded-full"
            style="background-color: var(--state-success); box-shadow: 0 0 0 2px var(--bg-deep);"
        ></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span>
<?php /**PATH /var/www/html/resources/views/components/dashy/avatar.blade.php ENDPATH**/ ?>