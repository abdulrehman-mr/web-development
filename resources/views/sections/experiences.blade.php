@php $dark = $s['background'] === 'dark'; @endphp
@if ($categories->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark" :href="route('packages.index')"/>

        @if ($s['style'] === 'pills')
            <div class="flex flex-wrap gap-3">
                @foreach ($categories as $category)
                    <a href="{{ route('packages.index', ['category' => $category->slug]) }}" @class(['group flex items-center gap-3 rounded-full p-1.5 pr-5 ring-1 transition', $dark ? 'bg-white/5 ring-white/15 hover:bg-white/10' : 'bg-white ring-slate-200 hover:ring-brand-300 hover:shadow-md'])>
                        <x-media :src="$category->image_url" class="size-11 shrink-0 rounded-full"/>
                        <span class="text-sm font-semibold">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div @class(['grid grid-cols-2 gap-3 lg:grid-cols-3 lg:gap-4', 'auto-rows-[11rem] sm:auto-rows-[14rem]' => $s['style'] === 'mosaic', 'sm:grid-cols-3' => $s['style'] === 'grid'])>
                @foreach ($categories as $category)
                    <a href="{{ route('packages.index', ['category' => $category->slug]) }}" @class(['group relative isolate block overflow-hidden rounded-2xl', 'aspect-[4/5] sm:aspect-[4/3]' => $s['style'] === 'grid', 'col-span-2 row-span-2' => $s['style'] === 'mosaic' && $loop->first, 'col-span-2 lg:col-span-1' => $s['style'] === 'mosaic' && $loop->last && $loop->count % 2 === 0]) data-reveal>
                        <x-media :src="$category->image_url" :alt="$category->name" class="absolute! inset-0 -z-10" img-class="transition duration-700 group-hover:scale-105"/>
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 text-white sm:p-5">
                            @php $big = $s['style'] === 'mosaic' && $loop->first; @endphp
                            <h3 @class(['font-bold tracking-tight', $big ? 'text-2xl sm:text-3xl' : 'text-base sm:text-lg'])>{{ $category->name }}</h3>
                            <p @class(['mt-0.5 text-white/80', $big ? 'text-sm sm:text-base' : 'hidden text-sm sm:block'])>{{ $category->tagline }}</p>
                            <p class="mt-1 text-xs text-white/70">{{ $category->packages_count }} {{ Str::plural('journey', $category->packages_count) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-builder.section>
@endif
