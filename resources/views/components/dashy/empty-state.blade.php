@props([
    'icon' => null,
    'iconVariant' => 'outline',
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class(['dashy-empty']) }}>
    @if ($icon)
        <x-dashy.icon :name="$icon" :variant="$iconVariant" class="dashy-empty-icon size-8" />
    @endif
    @if ($title)
        <p class="dashy-empty-title">{{ $title }}</p>
    @endif
    @if ($description)
        <p class="dashy-empty-description">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-2">
            {{ $action }}
        </div>
    @endif
</div>
