@php
    $dark = $s['background'] === 'dark';
    $shapes = ['arch' => 'arch aspect-[3/4]', 'rounded' => 'rounded-3xl aspect-[3/4]', 'circle' => 'rounded-full aspect-square', 'leaf' => 'rounded-tl-[4rem] rounded-br-[4rem] rounded-tr-xl rounded-bl-xl aspect-[3/4]'];
@endphp
@if ($destinations->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark" :href="route('destinations.index')"/>
        <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-3 lg:gap-x-8">
            @foreach ($destinations as $destination)
                <a href="{{ route('destinations.show', $destination) }}" @class(['group block', 'text-center' => $s['shape'] === 'circle']) data-reveal>
                    <x-media :src="$destination->image_url" :alt="$destination->name" :class="$shapes[$s['shape']]" img-class="transition duration-700 group-hover:scale-105"/>
                    <h3 @class(['mt-4 text-lg font-bold tracking-tight sm:text-xl', $dark ? 'text-white' : 'text-slate-900'])>{{ $destination->name }}</h3>
                    <p @class(['text-sm', $dark ? 'text-white/60' : 'text-slate-500'])>{{ $destination->region ?: $destination->country_name }} · {{ $destination->tagline }}</p>
                </a>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
