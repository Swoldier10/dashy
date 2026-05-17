<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => 'Select an option',
    'options' => [],            // associative ['value' => 'Label'] OR list of ['value' => …, 'label' => …]
    'searchPlaceholder' => null,
    'emptyMessage' => 'No results match your search.',
    'errorKey' => null,
    'showError' => true,
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
    'label' => null,
    'description' => null,
    'placeholder' => 'Select an option',
    'options' => [],            // associative ['value' => 'Label'] OR list of ['value' => …, 'label' => …]
    'searchPlaceholder' => null,
    'emptyMessage' => 'No results match your search.',
    'errorKey' => null,
    'showError' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use Illuminate\Support\Str;

    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    // Normalize options to a list of ['value' => string, 'label' => string].
    $normalized = [];
    foreach ($options as $key => $val) {
        if (is_array($val) && array_key_exists('value', $val) && array_key_exists('label', $val)) {
            $normalized[] = ['value' => (string) $val['value'], 'label' => (string) $val['label']];
        } else {
            $normalized[] = ['value' => (string) $key, 'label' => (string) $val];
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $idBase = 'dashy-searchable-select-' . str_replace(['.', '['], ['-', '-'], (string) ($name ?? uniqid()));
    $listboxId = $idBase . '-listbox';
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
        <p class="dashy-help" style="margin-top:-2px;"><?php echo e($description); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div
        x-data="dashySearchableSelect({
            modelName: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
            options: <?php echo \Illuminate\Support\Js::from($normalized)->toHtml() ?>,
            placeholder: <?php echo \Illuminate\Support\Js::from($placeholder)->toHtml() ?>,
            searchPlaceholder: <?php echo \Illuminate\Support\Js::from($searchPlaceholder ?? $placeholder)->toHtml() ?>,
            emptyMessage: <?php echo \Illuminate\Support\Js::from($emptyMessage)->toHtml() ?>,
        })"
        x-init="init()"
        @click.outside="close()"
        @keydown.escape.window="open && close()"
        class="dashy-searchable-select"
    >
        <div
            id="<?php echo e($idBase); ?>"
            role="combobox"
            tabindex="0"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-controls="<?php echo \Illuminate\Support\Js::from($listboxId)->toHtml() ?>"
            :data-open="open"
            @click="toggle()"
            @keydown.enter.prevent="open ? selectFocused() : openPanel()"
            @keydown.space.prevent="open ? null : openPanel()"
            @keydown.arrow-down.prevent="open ? focusNext() : openPanel()"
            @keydown.arrow-up.prevent="open ? focusPrev() : openPanel()"
            <?php echo e($attributes->whereDoesntStartWith(['wire:model', 'class'])->merge(['class' => 'dashy-searchable-select-trigger'])); ?>

        >
            <span
                x-show="! open"
                x-text="selectedLabel || placeholder"
                :class="! selectedLabel ? 'dashy-searchable-select-placeholder block truncate' : 'block truncate'"
            ></span>

            <input
                x-ref="search"
                x-show="open"
                x-model="search"
                type="search"
                role="searchbox"
                :placeholder="searchPlaceholder"
                :aria-controls="<?php echo \Illuminate\Support\Js::from($listboxId)->toHtml() ?>"
                aria-autocomplete="list"
                @click.stop
                @keydown.enter.stop.prevent="selectFocused()"
                @keydown.arrow-down.stop.prevent="focusNext()"
                @keydown.arrow-up.stop.prevent="focusPrev()"
                class="dashy-searchable-select-search"
            />

            <span class="dashy-searchable-select-chevron" aria-hidden="true">
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-down','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'size-4']); ?>
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
            </span>
        </div>

        <ul
            x-ref="listbox"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.120ms
            id="<?php echo e($listboxId); ?>"
            role="listbox"
            tabindex="-1"
            class="dashy-searchable-select-panel"
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $normalized; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $optValue = (string) $opt['value'];
                    $optLabel = (string) $opt['label'];
                    $optLabelLower = Str::lower($optLabel);
                ?>
                <li
                    role="option"
                    id="<?php echo e($idBase); ?>-option-<?php echo e($idx); ?>"
                    data-value="<?php echo e($optValue); ?>"
                    data-label-lower="<?php echo e($optLabelLower); ?>"
                    :aria-selected="String(value) === <?php echo \Illuminate\Support\Js::from($optValue)->toHtml() ?>"
                    :data-active="focusedValue === <?php echo \Illuminate\Support\Js::from($optValue)->toHtml() ?>"
                    x-show="! search || <?php echo \Illuminate\Support\Js::from($optLabelLower)->toHtml() ?>.includes(search.toLowerCase())"
                    @mouseenter="focusOption(<?php echo \Illuminate\Support\Js::from($optValue)->toHtml() ?>)"
                    @click="selectByValue(<?php echo \Illuminate\Support\Js::from($optValue)->toHtml() ?>)"
                    class="dashy-searchable-select-option"
                >
                    <span class="dashy-searchable-select-option-label"><?php echo e($optLabel); ?></span>
                    <span
                        x-show="String(value) === <?php echo \Illuminate\Support\Js::from($optValue)->toHtml() ?>"
                        class="dashy-searchable-select-option-check"
                        aria-hidden="true"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'check','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-4']); ?>
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
                    </span>
                </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <li
                x-show="visibleCount === 0"
                class="dashy-searchable-select-empty"
                x-text="emptyMessage"
            ></li>
        </ul>
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
<?php /**PATH /var/www/html/resources/views/components/dashy/searchable-select.blade.php ENDPATH**/ ?>