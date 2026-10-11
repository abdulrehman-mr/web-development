{{-- Hero: six layouts, each with its own media, text position, shade and search options. --}}
@php
    if ($isFirst && ! empty($heroOverride)) {
        $s = array_replace($s, array_filter($heroOverride));
    }
    foreach (['eyebrow', 'title', 'subtitle'] as $key) {
        $s[$key] = strtr((string) $s[$key], $placeholders ?? []);
    }

    $layout = $s['layout'];
    $onMedia = in_array($layout, ['background', 'slider', 'cards', 'banner'], true);
    $contained = $onMedia && $s['width'] === 'contained';
    $underHeader = $isFirst && ($overlay ?? false);
    $titleTag = $isFirst ? 'h1' : 'h2';

    $light = match ($s['text_color']) {
        'light' => true,
        'dark' => false,
        default => $onMedia ? $s['overlay_color'] !== 'light' : $s['background'] === 'dark',
    };
    $splitPhotoOnPhone = $layout === 'split' && $s['mobile_background'] && ! $light;

    $tones = [
        'light' => [
            'title' => 'text-white', 'sub' => 'text-white/85',
            'eyebrow' => 'bg-white/15 text-white ring-white/25 backdrop-blur',
            'btn1' => 'btn-light', 'btn2' => 'btn-ghost-light',
            'muted' => 'text-white/80', 'icon' => 'text-lake-300',
            'chip' => 'bg-white/15 text-white ring-white/25 backdrop-blur hover:bg-white hover:text-slate-900',
        ],
        'dark' => [
            'title' => 'text-slate-900', 'sub' => 'text-slate-600',
            'eyebrow' => 'bg-brand-50 text-brand-800 ring-brand-100',
            'btn1' => 'btn-primary', 'btn2' => 'btn-ghost',
            'muted' => 'text-slate-600', 'icon' => 'text-brand-600',
            'chip' => 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400',
        ],
        // White on the phone photo, dark on the desktop's plain background.
        'phone-light' => [
            'title' => 'text-white lg:text-slate-900', 'sub' => 'text-white/85 lg:text-slate-600',
            'eyebrow' => 'bg-white/15 text-white ring-white/25 backdrop-blur lg:bg-brand-50 lg:text-brand-800 lg:ring-brand-100 lg:backdrop-blur-none',
            'btn1' => 'bg-white text-brand-900 hover:bg-brand-50 lg:bg-brand-700 lg:text-white lg:hover:bg-brand-800',
            'btn2' => 'text-white ring-1 ring-white/40 hover:bg-white/10 lg:text-slate-900 lg:ring-slate-300 lg:hover:bg-slate-50',
            'muted' => 'text-white/80 lg:text-slate-600', 'icon' => 'text-lake-300 lg:text-brand-600',
            'chip' => 'bg-white/15 text-white ring-white/25 backdrop-blur lg:bg-white lg:text-slate-700 lg:ring-slate-200 lg:backdrop-blur-none',
        ],
    ];
    $tone = $tones[$splitPhotoOnPhone ? 'phone-light' : ($light ? 'light' : 'dark')];

    $backgrounds = ['white' => 'bg-white', 'light' => 'bg-slate-50', 'tint' => 'bg-brand-50', 'dark' => 'bg-brand-950'];
    $heights = [
        'auto' => 'py-16 sm:py-24',
        'medium' => 'min-h-[32rem] py-16 sm:min-h-[36rem]',
        'large' => 'min-h-[36rem] py-16 sm:min-h-[42rem] lg:min-h-[82vh]',
        'screen' => 'min-h-svh py-16',
    ];
    $xAlign = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end'][$s['content_x']];
    $yAlign = ['top' => 'items-start', 'center' => 'items-center', 'bottom' => 'items-end'][$s['content_y']];
    $align = $layout === 'cards' ? 'left' : $s['content_x'];

    // Photo shade, mixed from the chosen colour so it follows the colour scheme.
    $shadeColor = ['dark' => '#020617', 'brand' => 'var(--color-brand-950)', 'light' => '#ffffff'][$s['overlay_color']];
    $mix = fn (int|float $pct) => 'color-mix(in srgb, '.$shadeColor.' '.max(0, min(100, (int) round($pct))).'%, transparent)';
    $o = (int) $s['overlay_opacity'];
    $sideFrom = ['left' => 'to right', 'center' => 'to top', 'right' => 'to left'][$align];
    $shade = match ($s['overlay']) {
        'none' => null,
        'solid' => 'background:'.$mix($o),
        'side' => 'background:linear-gradient('.$sideFrom.', '.$mix($o + 15).' 0%, '.$mix($o * 0.55).' 45%, '.$mix($o * 0.1).' 100%)',
        'vignette' => 'background:radial-gradient(ellipse at center, '.$mix($o * 0.25).' 0%, '.$mix($o + 20).' 100%)',
        default => 'background:linear-gradient(to top, '.$mix($o + 25).' 0%, '.$mix($o * 0.6).' 50%, '.$mix($o * 0.25).' 100%)',
    };
    $edges = [
        'curve' => 'M0 0Q720 160 1440 0V80H0Z',
        'wave' => 'M0 40C240 80 480 0 720 40S1200 80 1440 40V80H0Z',
        'slant' => 'M0 80L1440 0V80Z',
    ];
    $edge = ! $contained && in_array($layout, ['background', 'slider', 'cards'], true) ? ($edges[$s['edge']] ?? null) : null;
    $edgeRoom = $edge ? 'pb-14 sm:pb-20 lg:pb-28' : '';
@endphp

<section id="section-{{ $section->id }}" @class(['relative isolate', 'container-x pt-4 sm:pt-6' => $contained])>
    @if ($onMedia)
        @php $box = $contained ? 'rounded-3xl overflow-hidden' : 'overflow-hidden'; @endphp

        @if ($layout === 'slider')
            <div x-data="heroSlider({{ count($slides) }}, {{ (int) $s['autoplay'] }})" @mouseenter="pause()" @mouseleave="play()"
                 @class(['relative isolate flex', $box, $heights[$s['height']], $yAlign, 'pt-28 sm:pt-32' => $underHeader])>
                @foreach ($slides as $i => $slide)
                    <div @class(['absolute inset-0 -z-20 transition-opacity duration-1000', $i === 0 ? 'opacity-100' : 'opacity-0']) :class="{ 'opacity-100': active === {{ $i }}, 'opacity-0': active !== {{ $i }} }">
                        <x-media :src="media_url($slide['image'])" alt="" :eager="$i === 0" class="absolute! inset-0"/>
                    </div>
                @endforeach
                @if ($shade)<div class="absolute inset-0 -z-10" style="{{ $shade }}"></div>@endif

                <div class="container-x flex {{ $xAlign }} {{ $edgeRoom }}">
                    @include('sections.partials.hero-content', ['slideText' => collect($slides)->map(fn ($slide) => ['title' => $slide['title'] ?? '', 'subtitle' => $slide['subtitle'] ?? ''])->all()])
                </div>

                @if (count($slides) > 1)
                    <div @class(['absolute inset-x-0 z-20 flex items-center justify-center gap-3', $edge ? 'bottom-12 sm:bottom-20 lg:bottom-24' : 'bottom-5 sm:bottom-8'])>
                        <button type="button" @click="prev()" class="grid size-10 place-items-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-slate-900" aria-label="Previous slide"><x-icon name="arrow-left" class="size-4"/></button>
                        @foreach ($slides as $i => $slide)
                            <button type="button" @click="go({{ $i }})" @class(['h-2 rounded-full bg-white transition-all', $i === 0 ? 'w-7' : 'w-2 opacity-50']) :class="{ 'w-7': active === {{ $i }}, 'w-2 opacity-50': active !== {{ $i }} }" aria-label="Slide {{ $i + 1 }}"></button>
                        @endforeach
                        <button type="button" @click="next()" class="grid size-10 place-items-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-slate-900" aria-label="Next slide"><x-icon name="arrow-right" class="size-4"/></button>
                    </div>
                @endif
                @include('sections.partials.hero-edge')
            </div>

        @elseif ($layout === 'banner')
            <div @class(['relative isolate flex min-h-[22rem] sm:min-h-[26rem]', $box, $yAlign, 'pt-24' => $underHeader])>
                @include('sections.partials.hero-media')
                <div class="flex w-full {{ $xAlign }} p-6 sm:p-10 lg:p-12">
                    @include('sections.partials.hero-content', ['compact' => true])
                </div>
            </div>

        @elseif ($layout === 'cards')
            <div @class(['relative isolate flex items-center', $box, $heights[$s['height']], 'pt-28 sm:pt-32' => $underHeader])>
                @include('sections.partials.hero-media')
                <div class="container-x grid items-center gap-10 lg:grid-cols-12 lg:gap-12 {{ $edgeRoom }}">
                    <div class="min-w-0 lg:col-span-5">
                        @include('sections.partials.hero-content')
                    </div>
                    <div class="min-w-0 lg:col-span-7" x-data>
                        <div x-ref="track" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:px-0">
                            @foreach ($cards as $card)
                                <a href="{{ $card['url'] }}" class="group relative isolate block aspect-[3/4] w-56 shrink-0 snap-start overflow-hidden rounded-2xl shadow-2xl ring-1 ring-white/20 sm:w-64">
                                    <x-media :src="$card['image']" :alt="$card['title']" class="absolute! inset-0 -z-10" img-class="transition duration-700 group-hover:scale-105"/>
                                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                        <p class="text-lg leading-tight font-bold">{{ $card['title'] }}</p>
                                        @if ($card['meta'])<p class="mt-1 line-clamp-2 text-xs text-white/75">{{ $card['meta'] }}</p>@endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if ($cards->count() > 2)
                            <div class="mt-4 hidden gap-2 sm:flex">
                                <button type="button" @click="$refs.track.scrollBy({ left: -280, behavior: 'smooth' })" class="grid size-10 place-items-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-slate-900" aria-label="Scroll back"><x-icon name="arrow-left" class="size-4"/></button>
                                <button type="button" @click="$refs.track.scrollBy({ left: 280, behavior: 'smooth' })" class="grid size-10 place-items-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-slate-900" aria-label="Scroll forward"><x-icon name="arrow-right" class="size-4"/></button>
                            </div>
                        @endif
                    </div>
                </div>
                @include('sections.partials.hero-edge')
            </div>

        @else {{-- background --}}
            <div @class(['relative isolate flex', $box, $heights[$s['height']], $yAlign, 'pt-28 sm:pt-32' => $underHeader])>
                @include('sections.partials.hero-media')
                <div class="container-x flex {{ $xAlign }} {{ $edgeRoom }}">
                    @include('sections.partials.hero-content')
                </div>
                @include('sections.partials.hero-edge')
            </div>
        @endif

    @elseif ($layout === 'mosaic')
        @php
            $columns = [[], [], []];
            foreach ($tiles->take(9)->values() as $i => $tile) {
                $columns[$i % 3][] = $tile;
            }
        @endphp
        <div class="{{ $backgrounds[$s['background']] }} overflow-hidden">
            <div class="container-x grid items-center gap-10 py-12 sm:py-16 lg:grid-cols-12 lg:gap-12 lg:py-20">
                <div @class(['min-w-0 lg:col-span-5', 'lg:order-2' => $s['image_side'] === 'left'])>
                    @include('sections.partials.hero-content')
                </div>
                <div @class(['relative min-w-0 lg:col-span-7', 'lg:order-1' => $s['image_side'] === 'left'])>
                    <div class="-mx-4 flex gap-3 overflow-x-auto px-4 [scrollbar-width:none] lg:hidden">
                        @foreach ($tiles->take(6) as $tile)
                            <x-media :src="$tile" class="h-52 w-40 shrink-0 rounded-[1.75rem]"/>
                        @endforeach
                    </div>
                    <div class="hidden h-[38rem] grid-cols-3 gap-4 lg:grid">
                        @foreach ($columns as $c => $column)
                            <div @class(['flex flex-col gap-4', '-mt-10' => $c === 1, 'mt-10' => $c !== 1])>
                                @foreach ($column as $tile)
                                    <x-media :src="$tile" :eager="$isFirst" class="min-h-0 flex-1 rounded-[2rem]"/>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @include('sections.partials.hero-feature-card')
                </div>
            </div>
        </div>

    @else {{-- split --}}
        @php
            $masks = ['none' => '', 'rounded' => 'rounded-3xl', 'arch' => 'arch', 'circle' => 'rounded-full', 'blob' => 'mask-blob', 'leaf' => 'rounded-tl-[7rem] rounded-br-[7rem] rounded-tr-2xl rounded-bl-2xl', 'tilt' => 'rotate-2 rounded-3xl ring-8 ring-white shadow-2xl'];
        @endphp
        <div class="relative isolate {{ $backgrounds[$s['background']] }}">
            @if ($splitPhotoOnPhone)
                <div class="absolute inset-x-0 top-0 -z-10 h-[31rem] sm:h-[34rem] lg:hidden">
                    <x-media :src="media_url($s['image'])" alt="" :eager="$isFirst" class="absolute! inset-0"/>
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/55 via-slate-950/40 to-slate-950/85"></div>
                </div>
            @endif
            <div class="container-x grid lg:grid-cols-12 lg:items-center lg:gap-14 lg:py-20">
                <div @class(['min-w-0 lg:col-span-7', 'lg:order-2' => $s['image_side'] === 'left', 'py-12 sm:py-16 lg:py-0' => ! $splitPhotoOnPhone])>
                    @include('sections.partials.hero-content', ['phonePhoto' => $splitPhotoOnPhone])
                </div>
                <div @class(['min-w-0 lg:col-span-5', 'lg:order-1' => $s['image_side'] === 'left', 'hidden lg:block' => $splitPhotoOnPhone, 'pb-12 lg:pb-0' => ! $splitPhotoOnPhone])>
                    <div class="relative">
                        <x-media :src="media_url($s['image'])" :alt="$s['title']" :eager="$isFirst" @class([$masks[$s['image_mask']], $s['image_mask'] === 'circle' ? 'aspect-square' : 'aspect-[4/5]'])/>
                        @include('sections.partials.hero-feature-card')
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
