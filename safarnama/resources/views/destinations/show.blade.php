<x-site-layout :title="$destination->name" :description="$destination->tagline">
    <x-page-hero :eyebrow="$destination->region" :title="$destination->name" :intro="$destination->tagline" :image="$destination->image_url">
        @if ($destination->best_season)
            <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm ring-1 ring-white/20 backdrop-blur"><x-icon name="sun" class="size-4 text-lake-300"/>Best time to visit: {{ $destination->best_season }}</p>
        @endif
    </x-page-hero>

    <div class="container-x grid gap-12 py-16 sm:py-20 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <h2 class="heading-md mb-4 text-slate-900">About {{ $destination->name }}</h2>
            <div class="prose-travel text-lg"><p>{{ $destination->description }}</p></div>
        </div>
        @if (! empty($destination->highlights))
            <div class="lg:col-span-5">
                <div class="rounded-3xl bg-brand-900 p-8 text-white">
                    <h2 class="text-2xl font-bold tracking-tight">Don’t miss</h2>
                    <ul class="mt-5 space-y-3">
                        @foreach ($destination->highlights as $h)
                            <li class="flex gap-3 text-white/85"><x-icon name="sparkle" class="mt-0.5 size-5 shrink-0 text-lake-300"/>{{ $h }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    @if ($destination->packages->isNotEmpty())
        <section class="bg-slate-100 py-16 sm:py-20">
            <div class="container-x">
                <x-section-heading :title="'Journeys that visit '.$destination->name"/>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($destination->packages as $package)
                        <x-package-card :package="$package"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($destination->hotels->isNotEmpty())
        <section class="py-16 sm:py-20">
            <div class="container-x">
                <x-section-heading :title="'Where we stay in '.$destination->name"/>
                <div class="mt-10 grid gap-6 md:grid-cols-2">
                    @foreach ($destination->hotels as $hotel)
                        @include('partials.hotel-card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($nearby->isNotEmpty())
        <section class="border-t border-slate-200 py-16">
            <div class="container-x">
                <h2 class="heading-md text-slate-900">More in {{ $destination->region }}</h2>
                <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3">
                    @foreach ($nearby as $d)
                        <a href="{{ route('destinations.show', $d) }}" class="group relative block overflow-hidden rounded-3xl">
                            <x-media :src="$d->image_url" :alt="$d->name" class="aspect-[4/3]" img-class="transition duration-700 group-hover:scale-105"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 to-transparent"></div>
                            <p class="absolute bottom-4 left-5 text-2xl font-bold tracking-tight text-white">{{ $d->name }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-site-layout>
