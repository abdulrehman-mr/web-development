@props(['title', 'eyebrow' => null, 'intro' => null, 'image' => null])
<section class="relative isolate overflow-hidden bg-pine-950 pt-32 pb-16 text-marble-50 sm:pt-40 sm:pb-24">
    <x-media :src="$image" class="absolute! inset-0 -z-10 opacity-45" eager/>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-pine-950 via-pine-950/60 to-pine-950/30"></div>
    <div class="container-x">
        @if ($eyebrow)
            <p class="eyebrow mb-4 text-brass-300">{{ $eyebrow }}</p>
        @endif
        <h1 class="heading-xl max-w-4xl">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-5 max-w-2xl text-base leading-relaxed text-marble-50/75 sm:text-lg">{{ $intro }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
