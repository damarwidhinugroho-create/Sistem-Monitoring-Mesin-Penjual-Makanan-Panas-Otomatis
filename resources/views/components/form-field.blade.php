@props([
    'label',
    'for',
    'error' => null,
    'labelClass' => 'form-field__label',
])

<div {{ $attributes->class('form-field') }}>
    <label class="{{ $labelClass }}" for="{{ $for }}">{{ $label }}</label>
    {{ $slot }}
    @if ($error)
        <p class="form-error" role="alert">{{ $error }}</p>
    @endif
</div>
