@php
    $priority = $priority ?? \App\Domains\Tasks\Enums\TaskPriority::Normal;
    $isMuted = $priority === \App\Domains\Tasks\Enums\TaskPriority::Normal;
    $sizeClass = $sizeClass ?? 'size-4';
@endphp
<x-dashy.icon
    :name="$isMuted ? 'flag' : 'flag'"
    :class="$sizeClass"
    style="color: var({{ $priority->colorVar() }});"
    aria-label="{{ $priority->label() }}"
/>
