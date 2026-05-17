<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([]));

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

foreach (array_filter(([]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="{
        focusItem(direction) {
            const items = this.items();
            if (! items.length) return;
            const current = items.indexOf(document.activeElement);
            let next = current;
            if (direction === 'next') next = current === -1 ? 0 : (current + 1) % items.length;
            else if (direction === 'prev') next = current === -1 ? items.length - 1 : (current - 1 + items.length) % items.length;
            else if (direction === 'first') next = 0;
            else if (direction === 'last') next = items.length - 1;
            items[next]?.focus();
        },
        items() {
            return Array.from(this.$el.querySelectorAll('[role=\'menuitem\']:not([disabled])'));
        },
    }"
    role="menu"
    @keydown.arrow-down.prevent="focusItem('next')"
    @keydown.arrow-up.prevent="focusItem('prev')"
    @keydown.home.prevent="focusItem('first')"
    @keydown.end.prevent="focusItem('last')"
    <?php echo e($attributes->class(['dashy-menu'])); ?>

>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/menu.blade.php ENDPATH**/ ?>