{{-- A photo with a branded fallback underneath, so a missing image never looks broken. --}}
@props(['src' => null, 'alt' => '', 'eager' => false, 'imgClass' => ''])
<div {{ $attributes->merge(['class' => 'media-fallback relative overflow-hidden']) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @if(! $eager) loading="lazy" @else fetchpriority="high" @endif decoding="async"
             class="absolute inset-0 size-full object-cover {{ $imgClass }}" onerror="this.remove()">
    @endif
    {{ $slot }}
</div>
