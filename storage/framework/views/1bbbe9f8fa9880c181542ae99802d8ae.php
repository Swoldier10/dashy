<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'side' => 'right', // left | right
    'size' => 'md',    // sm | md | lg
    'focusable' => true,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
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
    'name',
    'side' => 'right', // left | right
    'size' => 'md',    // sm | md | lg
    'focusable' => true,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $wireClose = $attributes->get('wire:close');
    $cardAttributes = $attributes->whereDoesntStartWith('wire:close');
?>

<div
    x-data="{
        name: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
        side: <?php echo \Illuminate\Support\Js::from($side)->toHtml() ?>,
        get isOpen() { return this.$store.modals.is(this.name); },
        release: null,
        wasOpen: false,
        playEnter() {
            // Run the slide-in animation once per open. Driving the
            // class from Alpine instead of the server-rendered HTML
            // prevents Livewire morph from re-asserting it on every
            // roundtrip — which would otherwise replay the entrance
            // every time a wire:click inside the drawer hits the server.
            const enterClass = 'dashy-drawer-card--' + this.side;
            this.$nextTick(() => {
                if (this.$refs.card) {
                    this.$refs.card.classList.remove(enterClass);
                    void this.$refs.card.offsetWidth;
                    this.$refs.card.classList.add(enterClass);
                }
                if (this.$refs.backdrop) {
                    this.$refs.backdrop.classList.remove('dashy-fade-in');
                    void this.$refs.backdrop.offsetWidth;
                    this.$refs.backdrop.classList.add('dashy-fade-in');
                }
            });
        },
        sync() {
            if (this.isOpen && ! this.wasOpen) {
                this.wasOpen = true;
                this.playEnter();
                <?php if($focusable): ?>
                    this.$nextTick(() => {
                        this.release = window.dashyTrapFocus(this.$refs.card);
                    });
                <?php endif; ?>
            } else if (! this.isOpen && this.wasOpen) {
                this.wasOpen = false;
                if (this.release) { this.release(); this.release = null; }
                <?php if($wireClose): ?>
                    if (typeof $wire?.<?php echo e($wireClose); ?> === 'function') {
                        $wire.<?php echo e($wireClose); ?>();
                    }
                <?php endif; ?>
            }
        },
    }"
    x-effect="sync()"
    @keydown.escape.window="<?php if($closeOnEscape): ?> if (isOpen && $store.modals.top() === name) $store.modals.close(name) <?php endif; ?>"
>
    
    <div x-show="$store.modals.is(<?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>)" x-cloak>
        <div
            x-ref="backdrop"
            class="dashy-modal-backdrop"
            <?php if($closeOnBackdrop): ?> @click="$store.modals.close(name)" <?php endif; ?>
        ></div>

        <div class="dashy-drawer-shell dashy-drawer-shell--<?php echo e($side); ?>">
            <div
                x-ref="card"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                data-modal-name="<?php echo e($name); ?>"
                <?php echo e($cardAttributes->class([
                    'dashy-drawer-card',
                    'dashy-drawer--' . $size,
                ])); ?>

            >
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/drawer.blade.php ENDPATH**/ ?>