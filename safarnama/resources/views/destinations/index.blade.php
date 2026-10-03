<x-site-layout title="Destinations">
    <x-page-hero eyebrow="Destinations" title="Where we travel" :intro="$count.' places across Pakistan, from the Arabian Sea to the Karakoram — each one we know first-hand.'" :image="media_url(setting('about_image'))"/>

    <div class="container-x space-y-20 py-16 sm:py-24">
        @foreach ($regions as $region => $destinations)
            <section>
                <div class="flex items-baseline justify-between gap-4 border-b border-marble-300 pb-4">
                    <h2 class="heading-md text-pine-950">{{ $region }}</h2>
                    <span class="text-sm text-ink/50">{{ $destinations->count() }} {{ Str::plural('place', $destinations->count()) }}</span>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
                    @foreach ($destinations as $destination)
                        <a href="{{ route('destinations.show', $destination) }}" class="group block" data-reveal>
                            <x-media :src="$destination->image_url" :alt="$destination->name" class="arch aspect-[3/4]" img-class="transition duration-700 group-hover:scale-105"/>
                            <h3 class="mt-4 font-display text-2xl font-semibold text-pine-950">{{ $destination->name }}</h3>
                            <p class="mt-0.5 text-sm text-ink/60">{{ $destination->tagline }}</p>
                            <p class="mt-2 text-xs font-medium text-brass-600">{{ $destination->packages_count }} {{ Str::plural('journey', $destination->packages_count) }} · Best {{ $destination->best_season }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    @include('partials.cta')
</x-site-layout>
