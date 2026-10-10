@props(['package'])
<a href="{{ route('packages.show', $package) }}" class="group block" data-reveal>
    <x-media :src="$package->hero_image_url" :alt="$package->title" class="aspect-[4/3] rounded-2xl" img-class="transition duration-500 group-hover:scale-[1.04]">
        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
            @if ($package->category)
                <span class="rounded-lg bg-white/95 px-2.5 py-1 text-xs font-semibold text-slate-800 shadow-sm">{{ $package->category->name }}</span>
            @endif
            @if ($package->original_price && $package->original_price > $package->price)
                <span class="rounded-lg bg-brand-700 px-2.5 py-1 text-xs font-semibold text-white">Save {{ money($package->original_price - $package->price) }}</span>
            @endif
        </div>
    </x-media>
    <div class="pt-4">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-lg leading-snug font-bold tracking-tight text-slate-900 group-hover:text-brand-700">{{ $package->title }}</h3>
            <span class="mt-0.5 inline-flex shrink-0 items-center gap-1 text-sm font-medium text-slate-900"><x-icon name="star" class="size-3.5"/>{{ number_format($package->rating, 1) }}</span>
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ $package->duration_days }} days @if ($package->start_city) · from {{ $package->start_city }} @endif @if ($package->best_season) · {{ $package->best_season }} @endif</p>
        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600">{{ $package->summary }}</p>
        <x-price class="mt-3" :amount="$package->price" :original="$package->original_price" size="sm"/>
    </div>
</a>
