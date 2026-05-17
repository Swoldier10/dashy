@props([
    'variant' => 'default', // default | subtle
    'orientation' => 'horizontal', // horizontal | vertical
])

@php
    $color = $variant === 'subtle' ? 'var(--border)' : 'var(--border-mid)';
@endphp

@if ($orientation === 'vertical')
    <span
        role="separator"
        aria-orientation="vertical"
        {{ $attributes->class(['inline-block w-px self-stretch']) }}
        style="background-color: {{ $color }};"
    ></span>
@else
    <hr
        {{ $attributes->class(['border-0 h-px w-full']) }}
        style="background-color: {{ $color }};"
    />
@endif
