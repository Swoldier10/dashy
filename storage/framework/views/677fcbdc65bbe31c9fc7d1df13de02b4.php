<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'value' => 1,
    'label' => null,
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
    'value' => 1,
    'label' => null,
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
        foreach ($attributes->getAttributes() as $key => $val) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($val) ? $val : null;
                break;
            }
        }
    }
?>

<label class="inline-flex items-center gap-2 cursor-pointer">
    <span class="dashy-checkbox">
        <input
            type="checkbox"
            <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
            value="<?php echo e($value); ?>"
            <?php echo e($attributes); ?>

        />
        <svg class="dashy-check size-3" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: var(--color-brand-cocoa);">
            <path d="M2.5 6.2 5 8.7l4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label || $slot->isNotEmpty()): ?>
        <span class="text-sm" style="color: var(--ink);"><?php echo e($label ?? $slot); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</label>
<?php /**PATH /var/www/html/resources/views/components/dashy/checkbox.blade.php ENDPATH**/ ?>