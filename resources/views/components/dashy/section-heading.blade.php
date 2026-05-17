@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['dashy-section-heading']) }}>
    <div class="min-w-0 flex-1">
        <h2 class="dashy-section-heading-title">{{ $title }}</h2>
        @if ($description)
            <p class="dashy-section-heading-description">{{ $description }}</p>
        @endif
    </div>
    @if (isset($action))
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
