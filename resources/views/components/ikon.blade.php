<span
    {{ $attributes->except(['size', 'active', 'large', 'white'])->class([
        'icon-placeholder',
        'icon-placeholder--large' => $large,
        'icon-placeholder--orange' => $active && ! $white,
        'icon-placeholder--white' => $white,
    ]) }}
    style="width: {{ $large ? 24 : $size }}px; height: {{ $large ? 24 : $size }}px"
    aria-hidden="true"
></span>
