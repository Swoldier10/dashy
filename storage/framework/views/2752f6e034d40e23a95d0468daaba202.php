<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'size' => 'md', // sm | md | lg | xl | 2xl
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
    'size' => 'md', // sm | md | lg | xl | 2xl
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
    // The Livewire-side close method is passed via wire:close="…".
    $wireClose = $attributes->get('wire:close');
    // Strip wire:close so it doesn't get reflected as an HTML attribute.
    $cardAttributes = $attributes->whereDoesntStartWith('wire:close');
?>

<div
    x-data="{
        name: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
        get isOpen() { return this.$store.modals.is(this.name); },
        release: null,
        wasOpen: false,
        playEnter() {
            // Run the open animation once per modal-open by toggling the
            // class on the next frame. Driving the animation from Alpine
            // (instead of leaving the class in the server-rendered HTML)
            // keeps Livewire morph from re-asserting it on every roundtrip
            // — which would otherwise replay the pop-in/fade-in every time
            // a wire:click inside the modal hits the server.
            this.$nextTick(() => {
                if (this.$refs.card) {
                    this.$refs.card.classList.remove('dashy-pop-in');
                    void this.$refs.card.offsetWidth;
                    this.$refs.card.classList.add('dashy-pop-in');
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

        <div class="dashy-modal-shell">
            <div
                x-ref="card"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                data-modal-name="<?php echo e($name); ?>"
                <?php echo e($cardAttributes->class([
                    'dashy-modal-card',
                    'dashy-modal--' . $size,
                ])); ?>

            >
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/modal.blade.php ENDPATH**/ ?>