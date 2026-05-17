@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => 'Select an option',
    'options' => [],            // associative ['value' => 'Label'] OR list of ['value' => …, 'label' => …]
    'searchPlaceholder' => null,
    'emptyMessage' => 'No results match your search.',
    'errorKey' => null,
    'showError' => true,
])

@php
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
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <x-dashy.label :for="$idBase">{{ $label }}</x-dashy.label>
    @endif

    @if ($description)
        <p class="dashy-help" style="margin-top:-2px;">{{ $description }}</p>
    @endif

    <div
        x-data="dashySearchableSelect({
            modelName: @js($name),
            options: @js($normalized),
            placeholder: @js($placeholder),
            searchPlaceholder: @js($searchPlaceholder ?? $placeholder),
            emptyMessage: @js($emptyMessage),
        })"
        x-init="init()"
        @click.outside="close()"
        @keydown.escape.window="open && close()"
        class="dashy-searchable-select"
    >
        <div
            id="{{ $idBase }}"
            role="combobox"
            tabindex="0"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-controls="@js($listboxId)"
            :data-open="open"
            @click="toggle()"
            @keydown.enter.prevent="open ? selectFocused() : openPanel()"
            @keydown.space.prevent="open ? null : openPanel()"
            @keydown.arrow-down.prevent="open ? focusNext() : openPanel()"
            @keydown.arrow-up.prevent="open ? focusPrev() : openPanel()"
            {{ $attributes->whereDoesntStartWith(['wire:model', 'class'])->merge(['class' => 'dashy-searchable-select-trigger']) }}
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
                :aria-controls="@js($listboxId)"
                aria-autocomplete="list"
                @click.stop
                @keydown.enter.stop.prevent="selectFocused()"
                @keydown.arrow-down.stop.prevent="focusNext()"
                @keydown.arrow-up.stop.prevent="focusPrev()"
                class="dashy-searchable-select-search"
            />

            <span class="dashy-searchable-select-chevron" aria-hidden="true">
                <x-dashy.icon name="chevron-down" class="size-4" />
            </span>
        </div>

        <ul
            x-ref="listbox"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.120ms
            id="{{ $listboxId }}"
            role="listbox"
            tabindex="-1"
            class="dashy-searchable-select-panel"
        >
            @foreach ($normalized as $idx => $opt)
                @php
                    $optValue = (string) $opt['value'];
                    $optLabel = (string) $opt['label'];
                    $optLabelLower = Str::lower($optLabel);
                @endphp
                <li
                    role="option"
                    id="{{ $idBase }}-option-{{ $idx }}"
                    data-value="{{ $optValue }}"
                    data-label-lower="{{ $optLabelLower }}"
                    :aria-selected="String(value) === @js($optValue)"
                    :data-active="focusedValue === @js($optValue)"
                    x-show="! search || @js($optLabelLower).includes(search.toLowerCase())"
                    @mouseenter="focusOption(@js($optValue))"
                    @click="selectByValue(@js($optValue))"
                    class="dashy-searchable-select-option"
                >
                    <span class="dashy-searchable-select-option-label">{{ $optLabel }}</span>
                    <span
                        x-show="String(value) === @js($optValue)"
                        class="dashy-searchable-select-option-check"
                        aria-hidden="true"
                    >
                        <x-dashy.icon name="check" class="size-4" />
                    </span>
                </li>
            @endforeach

            <li
                x-show="visibleCount === 0"
                class="dashy-searchable-select-empty"
                x-text="emptyMessage"
            ></li>
        </ul>
    </div>

    @if ($showError && $errorBagKey)
        <x-dashy.field-error :name="$errorBagKey" />
    @endif
</div>
