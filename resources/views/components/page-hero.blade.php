@props(['title', 'eyebrow' => null, 'intro' => null, 'image' => null])
<section class="pt-16 lg:pt-[4.5rem]">
    <div class="container-x pt-4 sm:pt-6">
        <div class="relative isolate flex min-h-[22rem] items-end overflow-hidden rounded-3xl text-white sm:min-h-[26rem]">
            <x-media :src="$image" alt="" eager class="absolute! inset-0 -z-10"/>
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/85 via-slate-950/35 to-slate-950/5"></div>
            <div class="max-w-3xl p-6 sm:p-10 lg:p-12">
                @if ($eyebrow)
                    <p class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-sm font-medium backdrop-blur">{{ $eyebrow }}</p>
                @endif
                <h1 class="heading-xl text-balance">{{ $title }}</h1>
                @if ($intro)
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/85 sm:text-lg">{{ $intro }}</p>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
