<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,           // Livewire model name (or HTML name attribute)
    'label' => null,
    'placeholder' => null,
    'minDate' => null,        // YYYY-MM-DD
    'maxDate' => null,        // YYYY-MM-DD
    'onChange' => null,       // Livewire method to call after a pick (e.g. "saveTaskDetail")
    'errorKey' => null,
    'showError' => true,
    'testId' => null,         // honoured for any inline data-test we want
    'withTime' => false,      // when true, panel includes 24h HH:MM inputs and value is YYYY-MM-DDTHH:mm
    'minuteStep' => 5,        // step for the minute input (1, 5, 15…)
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
    'name' => null,           // Livewire model name (or HTML name attribute)
    'label' => null,
    'placeholder' => null,
    'minDate' => null,        // YYYY-MM-DD
    'maxDate' => null,        // YYYY-MM-DD
    'onChange' => null,       // Livewire method to call after a pick (e.g. "saveTaskDetail")
    'errorKey' => null,
    'showError' => true,
    'testId' => null,         // honoured for any inline data-test we want
    'withTime' => false,      // when true, panel includes 24h HH:MM inputs and value is YYYY-MM-DDTHH:mm
    'minuteStep' => 5,        // step for the minute input (1, 5, 15…)
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
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $idBase = 'dashy-date-' . str_replace(['.', '['], ['-', '-'], (string) ($name ?? uniqid()));
?>

<div class="grid gap-1.5">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginal92099487053ef6086efd6f50c4bedaee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92099487053ef6086efd6f50c4bedaee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.label','data' => ['for' => $idBase]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($idBase)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $attributes = $__attributesOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__attributesOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92099487053ef6086efd6f50c4bedaee)): ?>
<?php $component = $__componentOriginal92099487053ef6086efd6f50c4bedaee; ?>
<?php unset($__componentOriginal92099487053ef6086efd6f50c4bedaee); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div
        x-data="dashyDatePicker({
            modelName: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
            minDate: <?php echo \Illuminate\Support\Js::from($minDate)->toHtml() ?>,
            maxDate: <?php echo \Illuminate\Support\Js::from($maxDate)->toHtml() ?>,
            onChange: <?php echo \Illuminate\Support\Js::from($onChange)->toHtml() ?>,
            withTime: <?php echo \Illuminate\Support\Js::from((bool) $withTime)->toHtml() ?>,
            minuteStep: <?php echo \Illuminate\Support\Js::from((int) $minuteStep)->toHtml() ?>,
        })"
        x-init="init()"
        @keydown="keyDown($event)"
        @click.outside="close()"
        @keydown.escape.window="open && close()"
        class="dashy-date-picker"
    >
        
        <div class="relative">
            <input
                id="<?php echo e($idBase); ?>"
                type="text"
                readonly
                x-model="display"
                :placeholder="placeholder || <?php echo \Illuminate\Support\Js::from($placeholder ?? __('Pick a date'))->toHtml() ?>"
                @click="toggle()"
                @keydown.enter.prevent="toggle()"
                @keydown.space.prevent="toggle()"
                <?php if($testId): ?> data-test="<?php echo e($testId); ?>" <?php endif; ?>
                <?php echo e($attributes->whereDoesntStartWith(['wire:model', 'class'])->merge(['class' => 'dashy-input cursor-pointer pr-10'])); ?>

            />
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--ink-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                    <path d="M8 3v3M16 3v3M3.5 9.5h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
                </svg>
            </span>
        </div>

        
        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.120ms
            class="dashy-date-panel dashy-pop-in"
            role="dialog"
            aria-label="<?php echo e(__('Choose a date')); ?>"
        >
            
            <div class="dashy-date-header">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-sm font-semibold" style="color: var(--ink);" x-text="MONTH_NAMES[month]"></span>
                    <span class="text-sm" style="color: var(--ink-muted);" x-text="year"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click.stop="prevMonth()"
                        class="dashy-date-nav"
                        aria-label="<?php echo e(__('Previous month')); ?>"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click.stop="nextMonth()"
                        class="dashy-date-nav"
                        aria-label="<?php echo e(__('Next month')); ?>"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            
            <div class="dashy-date-grid mb-1">
                <template x-for="day in DAYS_SHORT" :key="day">
                    <div class="dashy-date-weekday" x-text="day"></div>
                </template>
            </div>

            
            <div class="dashy-date-grid">
                <template x-for="blank in blankdays" :key="'b' + blank">
                    <div></div>
                </template>
                <template x-for="d in noOfDays" :key="d">
                    <button
                        type="button"
                        @click.stop="select(d)"
                        :disabled="!isInRange(d)"
                        :tabindex="focused === d ? 0 : -1"
                        :data-active="focused === d"
                        :class="{
                            'dashy-date-cell--selected': isSelected(d),
                            'dashy-date-cell--today': isToday(d) && !isSelected(d),
                            'dashy-date-cell--disabled': !isInRange(d),
                        }"
                        class="dashy-date-cell"
                        x-text="d"
                    ></button>
                </template>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($withTime): ?>
                <div class="dashy-date-time-row">
                    <span class="dashy-date-time-label"><?php echo e(__('Time')); ?></span>
                    <input
                        type="number"
                        min="0"
                        max="23"
                        step="1"
                        inputmode="numeric"
                        x-model.number="hour"
                        @input="onTimeInput()"
                        @blur="clampTime()"
                        @keydown.enter.prevent="clampTime(); close();"
                        class="dashy-date-time-input"
                        aria-label="<?php echo e(__('Hour')); ?>"
                    />
                    <span class="dashy-date-time-sep">:</span>
                    <input
                        type="number"
                        min="0"
                        max="59"
                        :step="minuteStep"
                        inputmode="numeric"
                        x-model.number="minute"
                        @input="onTimeInput()"
                        @blur="clampTime()"
                        @keydown.enter.prevent="clampTime(); close();"
                        class="dashy-date-time-input"
                        aria-label="<?php echo e(__('Minute')); ?>"
                    />
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="dashy-date-footer">
                <button
                    type="button"
                    @click.stop="select(new Date().getDate()); month = new Date().getMonth(); year = new Date().getFullYear(); recomputeGrid(); select(new Date().getDate());"
                    class="dashy-date-footer-btn"
                ><?php echo e(__('Today')); ?></button>
                <button
                    type="button"
                    @click.stop="clear()"
                    class="dashy-date-footer-btn"
                    x-show="value"
                    x-cloak
                ><?php echo e(__('Clear')); ?></button>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showError && $errorBagKey): ?>
        <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => $errorBagKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errorBagKey)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/date-picker.blade.php ENDPATH**/ ?>