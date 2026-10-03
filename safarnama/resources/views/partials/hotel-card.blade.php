<article class="group overflow-hidden rounded-3xl bg-white ring-1 ring-marble-200 sm:flex" data-reveal>
    <x-media :src="$hotel->image_url" :alt="$hotel->name" class="aspect-[16/10] sm:aspect-auto sm:w-2/5 sm:shrink-0" img-class="transition duration-700 group-hover:scale-105"/>
    <div class="p-6">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-medium text-brass-600">{{ $hotel->type }}</p>
            <div class="flex text-brass-500" aria-label="{{ $hotel->stars }} stars">@for ($i = 0; $i < $hotel->stars; $i++)<x-icon name="star" class="size-3.5"/>@endfor</div>
        </div>
        <h3 class="mt-1 font-display text-2xl font-semibold text-pine-950">{{ $hotel->name }}</h3>
        @if ($hotel->address)
            <p class="mt-1 flex items-center gap-1.5 text-xs text-ink/50"><x-icon name="map-pin" class="size-3.5"/>{{ $hotel->address }}</p>
        @endif
        <p class="mt-3 text-sm leading-relaxed text-ink/70">{{ $hotel->description }}</p>
        @if (! empty($hotel->amenities))
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($hotel->amenities as $a)
                    <span class="rounded-full bg-marble-100 px-2.5 py-1 text-xs text-ink/70">{{ $a }}</span>
                @endforeach
            </div>
        @endif
    </div>
</article>
