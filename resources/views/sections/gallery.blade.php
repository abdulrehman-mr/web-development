@php $dark = $s['background'] === 'dark'; $images = collect($s['images'])->filter(fn ($i) => filled($i['image'])); @endphp
@if ($images->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark"/>
        @if ($s['style'] === 'strip')
            <div class="-mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0">
                @foreach ($images as $item)
                    <figure class="w-64 shrink-0 snap-start sm:w-80">
                        <x-media :src="media_url($item['image'])" :alt="$item['caption']" class="aspect-[4/5] rounded-2xl"/>
                        @if ($item['caption'])<figcaption @class(['mt-2 text-sm', $dark ? 'text-white/70' : 'text-slate-600'])>{{ $item['caption'] }}</figcaption>@endif
                    </figure>
                @endforeach
            </div>
        @else
            <div @class(['grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3', 'auto-rows-[10rem] sm:auto-rows-[14rem]' => $s['style'] === 'mosaic'])>
                @foreach ($images as $item)
                    <figure @class(['group relative overflow-hidden rounded-2xl', 'row-span-2' => $s['style'] === 'mosaic' && $loop->index % 3 === 0, 'aspect-[4/3]' => $s['style'] === 'grid']) data-reveal>
                        <x-media :src="media_url($item['image'])" :alt="$item['caption']" class="absolute! inset-0" img-class="transition duration-700 group-hover:scale-105"/>
                        @if ($item['caption'])
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/75 to-transparent p-3 pt-8 text-sm text-white">{{ $item['caption'] }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</x-builder.section>
@endif
