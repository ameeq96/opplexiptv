@props([
    'image' => null,
    'alt' => '',
    'loading' => 'lazy',
    'decoding' => 'async',
    'sizes' => '(max-width: 767px) calc(100vw - 48px), 320px',
    'fetchpriority' => null,
    'width' => null,
    'height' => null,
])

@php
    $sources = is_array($image) ? $image : ['src' => (string) $image];
    $src = (string) ($sources['src'] ?? '');
    $renderWidth = $width ?: (isset($sources['width']) ? (int) $sources['width'] : null);
    $renderHeight = $height ?: (isset($sources['height']) ? (int) $sources['height'] : null);
@endphp

@if ($src !== '')
    <picture class="product-picture">
        @if (!empty($sources['avif_srcset']))
            <source type="image/avif" srcset="{{ $sources['avif_srcset'] }}" sizes="{{ $sizes }}">
        @endif
        @if (!empty($sources['webp_srcset']))
            <source type="image/webp" srcset="{{ $sources['webp_srcset'] }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $src }}"
            alt="{{ $alt }}"
            @if ($renderWidth) width="{{ $renderWidth }}" @endif
            @if ($renderHeight) height="{{ $renderHeight }}" @endif
            loading="{{ $loading }}"
            decoding="{{ $decoding }}"
            @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
            {{ $attributes }}>
    </picture>
@endif
