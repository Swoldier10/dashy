@php
    use App\Domains\Projects\Support\ProjectColor;
    use App\Domains\Projects\Support\ProjectIconShape;

    /** @var \App\Domains\Projects\Models\Project $project */
    $size = $size ?? 'sm'; // xs | sm | md
    $dimensions = match ($size) {
        'xs' => 14,
        'md' => 20,
        default => 16,
    };
    $colorVar = ProjectColor::for($project);
    $shape = ProjectIconShape::for($project);
@endphp

@if ($project->logo)
    <img
        src="{{ $project->logo }}"
        alt=""
        class="shrink-0 rounded object-cover"
        style="width: {{ $dimensions }}px; height: {{ $dimensions }}px;"
    />
@else
    <span
        class="inline-flex shrink-0 items-center justify-center"
        style="width: {{ $dimensions }}px; height: {{ $dimensions }}px;"
        aria-hidden="true"
    >
        @if ($shape === ProjectIconShape::CIRCLE)
            <span class="inline-block rounded-full"
                  style="width: {{ $dimensions - 4 }}px; height: {{ $dimensions - 4 }}px; background-color: var({{ $colorVar }});"></span>
        @elseif ($shape === ProjectIconShape::TRIANGLE)
            <svg viewBox="0 0 10 10" width="{{ $dimensions - 2 }}" height="{{ $dimensions - 2 }}" fill="var({{ $colorVar }})">
                <polygon points="5,1 9,9 1,9"></polygon>
            </svg>
        @else
            <svg viewBox="0 0 12 12" width="{{ $dimensions }}" height="{{ $dimensions }}" fill="none"
                 stroke="var({{ $colorVar }})" stroke-width="2" stroke-linecap="round">
                <line x1="6" y1="2" x2="6" y2="10"></line>
                <line x1="2" y1="6" x2="10" y2="6"></line>
            </svg>
        @endif
    </span>
@endif
