<div {{ $attributes->class('alert-banner') }}>
    <span class="alert-banner__icon"><x-ikon name="alert" /></span>
    <span class="alert-banner__message">{{ $message }}</span>
    @if ($linkLabel && $linkRoute)
        <a class="alert-banner__link" href="{{ route($linkRoute) }}">{{ $linkLabel }}</a>
    @endif
</div>
