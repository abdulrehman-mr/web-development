@php $dark = $s['background'] === 'dark'; @endphp
@if ($embed || $s['video'])
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x max-w-5xl">
        <x-builder.heading :s="$s" :dark="$dark" center/>
        <div class="relative aspect-video overflow-hidden rounded-3xl bg-slate-900 shadow-2xl">
            @if ($embed)
                <iframe src="{{ $embed['src'] }}" title="{{ $s['title'] ?: 'Video' }}" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" class="absolute inset-0 size-full border-0"></iframe>
            @else
                <video class="absolute inset-0 size-full object-cover" controls playsinline preload="metadata" @if ($s['image']) poster="{{ media_url($s['image']) }}" @endif>
                    <source src="{{ media_url($s['video']) }}">
                </video>
            @endif
        </div>
    </div>
</x-builder.section>
@endif
