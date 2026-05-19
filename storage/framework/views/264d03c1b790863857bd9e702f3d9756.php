<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'end',       // start | center | end
    'position' => 'bottom', // bottom | top
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
    'align' => 'end',       // start | center | end
    'position' => 'bottom', // bottom | top
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



<div
    x-data="dashyPopover('<?php echo e($align); ?>', '<?php echo e($position); ?>')"
    @keydown.escape.window="open && close()"
    @scroll.window.passive="open && reposition()"
    @resize.window.passive="open && reposition()"
    <?php echo e($attributes->class(['relative inline-block'])); ?>

>
    <div x-ref="trigger" @click="toggle()" class="contents">
        <?php echo e($trigger ?? ''); ?>

    </div>

    
    <div
        x-ref="panel"
        wire:ignore.self
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
        @click.outside="close()"
        <?php if($closeOnClickInside): ?> @click="setTimeout(() => close(), 0)" <?php endif; ?>
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'dashy-popover-panel',
            'dashy-pop-in',
            'dashy-popover-panel--align-' . $align,
            'dashy-popover-panel--position-' . $position,
            $panelClass,
        ]); ?>"
    >
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/popover.blade.php ENDPATH**/ ?>