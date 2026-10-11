@php $shapes = ['arch' => 'arch aspect-[3/4]', 'rounded' => 'rounded-3xl aspect-[3/4]', 'circle' => 'rounded-full aspect-square', 'leaf' => 'rounded-tl-[4rem] rounded-br-[4rem] rounded-tr-xl rounded-bl-xl aspect-[3/4]']; @endphp
<x-builder.section :section="$section">
    <div class="container-x space-y-20">
        @foreach ($regions as $region => $destinations)
            <div>
                <div class="flex items-baseline justify-between gap-4 border-b border-slate-300 pb-4">
                    <h2 class="heading-md text-slate-900">{{ $region }}</h2>
                    <span class="text-sm text-ink/50">{{ $destinations->count() }} {{ Str::plural('place', $destinations->count()) }}</span>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
                    @foreach ($destinations as $destination)
                        <a href="{{ route('destinations.show', $destination) }}" class="group block" data-reveal>
                            <x-media :src="$destination->image_url" :alt="$destination->name" :class="$shapes[$s['shape']]" img-class="transition duration-700 group-hover:scale-105"/>
                            <h3 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ $destination->name }}</h3>
                            <p class="mt-0.5 text-sm text-ink/60">{{ $destination->tagline }}</p>
                            @if ($multipleCountries && $destination->region)<p class="mt-1 text-xs text-slate-500">{{ $destination->region }}</p>@endif
                            <p class="mt-2 text-xs font-medium text-brand-600">{{ $destination->packages_count }} {{ Str::plural('journey', $destination->packages_count) }}@if ($destination->best_season) · Best {{ $destination->best_season }}@endif</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-builder.section>
