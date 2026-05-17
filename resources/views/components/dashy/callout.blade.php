@props([
    'variant' => 'info', // info | success | warning | danger
    'icon' => null,
    'title' => null,
])

@php
    $defaultIcon = match ($variant) {
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'danger' => 'x-circle',
        default => 'information-circle',
    };
    $iconName = $icon ?? $defaultIcon;
@endphp

<div {{ $attributes->class(['dashy-callout', 'dashy-callout--' . $variant]) }} role="status">
    <x-dashy.icon :name="$iconName" class="size-5 dashy-callout-icon" />
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="text-sm font-medium" style="color: var(--ink);">{{ $title }}</p>
        @endif
        <div @class(['text-sm', 'mt-1' => $title]) style="color: var(--ink-muted);">
            {{ $slot }}
        </div>
    </div>
</div>
