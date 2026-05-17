@php
    /**
     * @var array<int, array{key:string,label:string,count:int,colorVar:string,anchor:string}> $buckets
     * @var int $openCount
     * @var int $doneCount
     */
    $buckets = $buckets ?? [];
    $openCount = $openCount ?? 0;
    $doneCount = $doneCount ?? 0;
@endphp

<div class="flex flex-wrap items-center gap-2" data-test="status-summary">
    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
        @foreach ($buckets as $bucket)
            <span
                wire:key="summary-{{ $bucket['key'] }}"
                class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium"
                style="
                    background-color: color-mix(in srgb, var({{ $bucket['colorVar'] }}) 14%, transparent);
                    color: color-mix(in srgb, var({{ $bucket['colorVar'] }}) 80%, var(--ink));
                "
                data-test="status-summary-{{ $bucket['key'] }}"
            >
                <span class="inline-block size-1 rounded-full" style="background-color: var({{ $bucket['colorVar'] }});"></span>
                <span>{{ $bucket['label'] }}</span>
                <span style="color: color-mix(in srgb, var({{ $bucket['colorVar'] }}) 70%, var(--ink-dim));">{{ $bucket['count'] }}</span>
            </span>
        @endforeach
    </div>

    <div class="ml-auto whitespace-nowrap text-[11px]" style="color: var(--ink-dim);" data-test="status-summary-totals">
        <span><span style="color: var(--ink-muted);">{{ $openCount }}</span> {{ __('open') }}</span>
        <span class="mx-1">·</span>
        <span><span style="color: var(--ink-muted);">{{ $doneCount }}</span> {{ __('done') }}</span>
    </div>
</div>
