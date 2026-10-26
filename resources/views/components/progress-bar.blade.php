<span {{ $attributes->class(['progress', 'progress--'.$tone()]) }}>
    <span class="progress__track"><span class="progress__fill" style="width: {{ $percentage }}%"></span></span>
    @if ($label !== null)
        <span class="progress__label">{{ $label }}</span>
    @endif
</span>
