@props(['type' => 'text', 'size' => null])

@php
    $classes = ['skeleton', 'skeleton--'.$type];

    if ($size) {
        $classes[] = 'skeleton--'.$size;
    }
@endphp

<span {{ $attributes->class($classes) }} aria-hidden="true">
    @if ($type === 'chart')
        <span class="skeleton__chart-bars" aria-hidden="true">
            @foreach (range(1, 8) as $bar)
                <span></span>
            @endforeach
        </span>
        <span class="skeleton__chart-axis" aria-hidden="true">
            @foreach (range(1, 5) as $label)
                <span></span>
            @endforeach
        </span>
    @endif
</span>
