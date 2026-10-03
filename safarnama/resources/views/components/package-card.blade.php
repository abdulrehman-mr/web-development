@props(['package'])
<a href="{{ route('packages.show', $package) }}" class="group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-marble-200 transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_60px_-28px_rgb(8_26_22/0.45)]" data-reveal>
    <x-media :src="$package->hero_image_url" :alt="$package->title" class="aspect-[4/3]" img-class="transition duration-700 group-hover:scale-105">
        <div class="absolute inset-x-0 top-0 flex items-start justify-between p-4">
            @if ($package->category)
                <span class="rounded-full bg-pine-950/70 px-3 py-1 text-xs font-medium text-marble-50 backdrop-blur">{{ $package->category->name }}</span>
            @endif
            @if ($package->original_price && $package->original_price > $package->price)
                <span class="rounded-full bg-brass-400 px-3 py-1 text-xs font-semibold text-pine-950">Save {{ money($package->original_price - $package->price) }}</span>
            @endif
        </div>
    </x-media>
    <div class="flex flex-1 flex-col p-6">
        <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink/60">
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-brass-600"/>{{ $package->duration_days }} days</span>
            @if ($package->start_city)
                <span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" class="size-4 text-brass-600"/>From {{ $package->start_city }}</span>
            @endif
            <span class="inline-flex items-center gap-1"><x-icon name="star" class="size-3.5 text-brass-500"/>{{ number_format($package->rating, 1) }}</span>
        </div>
        <h3 class="font-display text-2xl leading-snug font-semibold text-pine-950 group-hover:text-pine-700">{{ $package->title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink/65">{{ $package->summary }}</p>
        <div class="mt-auto flex items-end justify-between gap-4 pt-6">
            <x-price :amount="$package->price" :original="$package->original_price"/>
            <span class="grid size-11 shrink-0 place-items-center rounded-full border border-pine-900/15 text-pine-900 transition group-hover:border-pine-800 group-hover:bg-pine-800 group-hover:text-marble-50">
                <x-icon name="arrow-right" class="size-5"/>
            </span>
        </div>
    </div>
</a>
