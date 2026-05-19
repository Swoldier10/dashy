
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url' => '',
    'duration' => null,
    'compact' => false,
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
    'url' => '',
    'duration' => null,
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $compact = (bool) $compact;
    $duration = is_numeric($duration) ? (float) $duration : 0;
?>

<div
    x-data="{
        knownDuration: <?php echo e($duration); ?>,
        currentTime: 0,
        playing: false,
        format(seconds) {
            const total = Math.max(0, Math.floor(Number.isFinite(seconds) ? seconds : 0));
            const m = Math.floor(total / 60);
            const s = (total % 60).toString().padStart(2, '0');
            return m + ':' + s;
        },
        get duration() {
            const fromAudio = this.$refs.audio?.duration;
            if (Number.isFinite(fromAudio) && fromAudio > 0) return fromAudio;
            return this.knownDuration;
        },
        progressPercent() {
            const d = this.duration;
            return d > 0 ? Math.min(100, (this.currentTime / d) * 100) : 0;
        },
        toggle() {
            const audio = this.$refs.audio;
            if (!audio) return;
            if (audio.paused) {
                audio.play().catch(err => console.error('Audio playback failed', err));
            } else {
                audio.pause();
            }
        },
        seek(event) {
            const audio = this.$refs.audio;
            if (!audio || this.duration <= 0) return;
            const bar = event.currentTarget;
            const rect = bar.getBoundingClientRect();
            const ratio = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
            audio.currentTime = ratio * this.duration;
        },
    }"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'flex items-center gap-2 rounded-full',
        'px-2 py-1.5' => $compact,
        'px-3 py-2' => ! $compact,
    ]); ?>"
    style="background-color: var(--surface-2);"
>
    
    <audio
        x-ref="audio"
        src="<?php echo e($url); ?>"
        preload="metadata"
        x-on:loadedmetadata="window.fixWebmDuration?.($refs.audio)"
        x-on:play="playing = true"
        x-on:pause="playing = false"
        x-on:ended="playing = false; currentTime = 0"
        x-on:timeupdate="currentTime = $refs.audio.currentTime"
        class="hidden"
    ></audio>

    <button
        type="button"
        x-on:click="toggle()"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'flex shrink-0 items-center justify-center rounded-full transition',
            'size-7' => $compact,
            'size-8' => ! $compact,
        ]); ?>"
        style="background-color: var(--accent); color: var(--bg-deep);"
        x-bind:aria-label="playing ? '<?php echo e(__('Pause')); ?>' : '<?php echo e(__('Play')); ?>'"
        data-test="audio-bubble-toggle"
    >
        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'play','class' => 'size-3.5','xShow' => '!playing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'play','class' => 'size-3.5','x-show' => '!playing']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'pause','class' => 'size-3.5','xShow' => 'playing','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pause','class' => 'size-3.5','x-show' => 'playing','x-cloak' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
    </button>

    <button
        type="button"
        x-on:click="seek($event)"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'relative h-1.5 flex-1 overflow-hidden rounded-full',
            'min-w-[80px]' => $compact,
            'min-w-[140px]' => ! $compact,
        ]); ?>"
        style="background-color: var(--border-mid);"
        aria-label="<?php echo e(__('Seek')); ?>"
    >
        <span
            class="absolute left-0 top-0 h-full rounded-full transition-[width]"
            style="background-color: var(--accent);"
            x-bind:style="`width: ${progressPercent()}%`"
        ></span>
    </button>

    <span
        class="shrink-0 text-xs tabular-nums"
        style="color: var(--ink-muted);"
        x-text="format(currentTime) + ' / ' + format(duration)"
    ></span>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/audio-bubble.blade.php ENDPATH**/ ?>