@props([
    'title' => null,
    'description' => null,
    'headingId' => null,
])

<section {{ $attributes->class('surface') }}>
    @if (isset($header))
        {{ $header }}
    @elseif ($title || $description || isset($actions))
        <header class="panel__header">
            <div class="panel__heading">
                @if ($title)
                    <h2 class="panel__title" @if ($headingId) id="{{ $headingId }}" @endif>{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="panel__description">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="panel__actions">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    {{ $slot }}

    @isset($footer)
        <footer class="panel__footer">{{ $footer }}</footer>
    @endisset
</section>
