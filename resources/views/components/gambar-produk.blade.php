@props(['name'])

@php
    $productSlug = \Illuminate\Support\Str::slug($name);
    $productImages = ['mie-tarempa', 'luti-gendang', 'mie-sagu'];
    $imagePath = in_array($productSlug, $productImages, true)
        ? 'images/products/'.$productSlug.'.svg'
        : 'images/products/default.svg';
@endphp

<img {{ $attributes->merge(['src' => asset($imagePath), 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async']) }}>
