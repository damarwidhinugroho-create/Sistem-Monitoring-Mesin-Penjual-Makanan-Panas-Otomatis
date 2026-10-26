@props(['name', 'size' => 16, 'color' => 'currentColor'])

<svg
    {{ $attributes->merge(['class' => 'icon']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    style="color: {{ $color }}"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('logo')
        @case('machine')
            <path d="M5 3h14a2 2 0 0 1 2 2v16H3V5a2 2 0 0 1 2-2Z" />
            <path d="M7 6h10v5H7zM7 14h4v4H7zM14 14h3" />
            <circle cx="16" cy="16" r="1" />
            @break
        @case('dashboard')
            <rect x="3" y="3" width="8" height="8" rx="1" />
            <rect x="13" y="3" width="8" height="5" rx="1" />
            <rect x="13" y="10" width="8" height="11" rx="1" />
            <rect x="3" y="13" width="8" height="8" rx="1" />
            @break
        @case('bell')
            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
            @break
        @case('notification-dot')
            <circle cx="12" cy="12" r="4" fill="currentColor" stroke="none" />
            @break
        @case('box')
            <path d="m12 3 9 5-9 5-9-5 9-5Z" />
            <path d="M3 8v9l9 5 9-5V8M12 13v9" />
            @break
        @case('box-empty')
            <path d="m12 3 9 5-9 5-9-5 9-5Z" />
            <path d="M3 8v9l9 5 9-5V8M12 13v9m-7-13 14 8" />
            @break
        @case('cart')
            <path d="M3 4h2l2.5 12h11l2-8H6" />
            <circle cx="9" cy="20" r="1" />
            <circle cx="17" cy="20" r="1" />
            @break
        @case('thermometer')
            <path d="M14 14.8V5a2 2 0 0 0-4 0v9.8a4 4 0 1 0 4 0Z" />
            <path d="M12 11v6" />
            @break
        @case('report')
            <path d="M6 3h8l5 5v13H6zM14 3v5h5M9 13h7m-7 4h7" />
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5m5 5H3" />
            <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
            @break
        @case('money')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <circle cx="12" cy="12" r="3" />
            <path d="M7 9h.01M17 15h.01" />
            @break
        @case('receipt')
            <path d="M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6m-6 4h6m-6 4h4" />
            @break
        @case('food')
            <path d="M3 12h18a9 9 0 0 1-18 0Z" />
            <path d="M5 8c1-2 3-2 4 0m2-2c1-2 3-2 4 0m2 2c1-2 2-2 3-1" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break
        @case('alert-triangle')
        @case('alert')
            <path d="m10.3 4.3-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-2.7l-8-14a2 2 0 0 0-3.4 0Z" />
            <path d="M12 9v4m0 4h.01" />
            @break
        @case('chevron-right')
            <path d="m9 18 6-6-6-6" />
            @break
        @default
            @php throw new \InvalidArgumentException("Ikon tidak dikenal: {$name}"); @endphp
    @endswitch
</svg>
