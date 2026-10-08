<article {{ $attributes->class('chart-card') }}>
    <div class="chart-card__header">
        <h2 class="chart-card__title">{{ $title }}</h2>
        @if ($controls)
            <div class="chart-card__controls">
                @foreach ($controls as $control)
                    <button
                        class="chart-card__control {{ $control === $activeControl ? 'chart-card__control--active' : '' }}"
                        type="button"
                        data-chart-period="{{ $control }}"
                    >{{ $control }}</button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="chart-card__plot">
        <canvas
            id="{{ $chartId }}"
            class="chart-card__canvas"
            data-chart-type="{{ $type }}"
            data-chart-labels='@json($labels)'
            data-chart-values='@json($values)'
            data-chart-secondary='@json($secondaryValues)'
            data-chart-alt-labels='@json($alternateLabels)'
            data-chart-alt-values='@json($alternateValues)'
            aria-label="{{ $title }}"
            role="img"
        ></canvas>
    </div>
</article>
