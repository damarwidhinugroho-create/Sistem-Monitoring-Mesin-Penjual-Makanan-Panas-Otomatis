<article {{ $attributes->class('stat-card') }}>
    <div>
        @if ($icon)
            <span class="stat-card__icon"><x-ikon /></span>
        @endif
        <span class="stat-card__label">{{ $label }}</span>
    </div>
    @if ($tag)
        <span class="stat-card__tag stat-card__tag--{{ $tagStatus }}">{{ $tag }}</span>
    @endif
    <strong class="stat-card__value">{{ $value }}</strong>
    @if ($detail)
        <small class="stat-card__detail">{{ $detail }}</small>
    @endif
</article>
