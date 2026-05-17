@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'type' => 'text',
    'viewable' => false,         // password show/hide toggle
    'icon' => null,              // leading icon
    'iconTrailing' => null,      // trailing icon
    'errorKey' => null,          // override the validation-error bag key
    'showError' => true,
])

@php
    // Infer name from any wire:model.* attribute if not given.
    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $hasIcon = (bool) $icon;
    $hasTrailingIcon = (bool) $iconTrailing || ($viewable && $type === 'password');
    $id = $attributes->get('id') ?? ($name ? 'dashy-input-' . str_replace(['.', '['], ['-', '-'], (string) $name) : null);
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <x-dashy.label :for="$id">{{ $label }}</x-dashy.label>
    @endif

    @if ($description)
        <p class="dashy-help" style="margin-top:-2px;">{{ $description }}</p>
    @endif

    <div
        @class([
            'relative' => $hasIcon || $hasTrailingIcon,
        ])
        @if ($viewable && $type === 'password') x-data="{ visible: false }" @endif
    >
        @if ($hasIcon)
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2" style="color: var(--ink-muted);">
                <x-dashy.icon :name="$icon" class="size-4" />
            </span>
        @endif

        <input
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            @if ($viewable && $type === 'password')
                x-bind:type="visible ? 'text' : 'password'"
            @else
                type="{{ $type }}"
            @endif
            {{ $attributes->merge(['class' => 'dashy-input'])->class([
                'pl-10' => $hasIcon,
                'pr-10' => $hasTrailingIcon,
            ]) }}
        />

        @if ($viewable && $type === 'password')
            <button
                type="button"
                @click="visible = ! visible"
                class="absolute right-3 top-1/2 -translate-y-1/2 rounded p-0.5"
                style="color: var(--ink-muted);"
                onmouseover="this.style.color='var(--ink)';"
                onmouseout="this.style.color='var(--ink-muted)';"
                tabindex="-1"
                aria-label="{{ __('Toggle password visibility') }}"
            >
                <span x-show="!visible"><x-dashy.icon name="eye" class="size-4" /></span>
                <span x-show="visible" x-cloak><x-dashy.icon name="eye-slash" class="size-4" /></span>
            </button>
        @elseif ($iconTrailing)
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--ink-muted);">
                <x-dashy.icon :name="$iconTrailing" class="size-4" />
            </span>
        @endif
    </div>

    @if ($showError && $errorBagKey)
        <x-dashy.field-error :name="$errorBagKey" />
    @endif
</div>
