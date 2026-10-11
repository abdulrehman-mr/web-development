<x-site-layout :title="$package->title" :description="$package->summary">
    @php
        $stays = $package->stays();
        $pricing = app(\App\Support\BookingPricing::class);
        $rooms = $pricing->rooms($package);
    @endphp

    {{-- Hero --}}
    <section class="relative isolate mt-16 overflow-hidden bg-slate-900 text-white lg:mt-[4.5rem]">
        <x-media :src="$package->hero_image_url" :alt="$package->title" eager class="absolute! inset-0 -z-10"/>
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950 via-slate-950/55 to-slate-950/20"></div>
        <div class="container-x flex min-h-[68vh] flex-col justify-end pt-24 pb-10 sm:pb-14">
            <nav class="mb-6 flex items-center gap-2 text-xs text-white/70" aria-label="Breadcrumb">
                <a href="{{ route('packages.index') }}" class="hover:text-white">Journeys</a>
                @if ($package->category)
                    <span>/</span><a href="{{ route('packages.index', ['category' => $package->category->slug]) }}" class="hover:text-white">{{ $package->category->name }}</a>
                @endif
            </nav>
            <h1 class="heading-xl max-w-4xl text-balance">{{ $package->title }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-white/80">{{ $package->summary }}</p>
            <dl class="mt-10 grid grid-cols-2 gap-px overflow-hidden rounded-2xl bg-white/15 ring-1 ring-white/15 backdrop-blur sm:grid-cols-4">
                @foreach ([
                    ['clock', 'Duration', $package->duration_days.' days · '.$package->nights.' nights'],
                    ['users', 'Group', $package->group_size ?: 'Private'],
                    ['sun', 'Best season', $package->best_season ?: 'Year-round'],
                    ['mountain', 'Pace', $package->difficulty],
                ] as [$icon, $label, $value])
                    <div class="bg-slate-950/45 p-4 sm:p-5">
                        <dt class="flex items-center gap-2 text-xs text-white/60"><x-icon :name="$icon" class="size-4 text-lake-300"/>{{ $label }}</dt>
                        <dd class="mt-1 text-sm font-semibold sm:text-base">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="container-x grid gap-12 py-14 sm:py-20 lg:grid-cols-12 lg:gap-16">
        <div class="min-w-0 space-y-16 lg:col-span-7 xl:col-span-8">
            {{-- Overview --}}
            <section>
                <h2 class="heading-md mb-4 text-slate-900">Overview</h2>
                <div class="prose-travel text-lg">
                    @foreach (preg_split('/\n\s*\n/', (string) $package->overview) as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                </div>
                @if ($package->destinations->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($package->destinations as $d)
                            <a href="{{ route('destinations.show', $d) }}" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-sm text-slate-900 ring-1 ring-slate-200 transition hover:bg-brand-800 hover:text-white"><x-icon name="map-pin" class="size-3.5"/>{{ $d->name }}</a>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Highlights --}}
            @if (! empty($package->highlights))
                <section>
                    <h2 class="heading-md text-slate-900">Highlights</h2>
                    <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($package->highlights as $h)
                            <li class="flex gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200"><x-icon name="sparkle" class="mt-0.5 size-5 shrink-0 text-brand-600"/><span class="text-ink/80">{{ $h }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Itinerary --}}
            @if ($package->days->isNotEmpty())
                <section x-data="{ open: [0], all: false }">
                    <div class="flex items-end justify-between gap-4">
                        <h2 class="heading-md text-slate-900">Day by day</h2>
                        <button type="button" class="text-sm font-semibold text-brand-700 hover:underline"
                                @click="all = !all; open = all ? [...Array({{ $package->days->count() }}).keys()] : []"
                                x-text="all ? 'Collapse all' : 'Expand all'">Expand all</button>
                    </div>
                    <ol class="relative mt-8 space-y-3 before:absolute before:top-4 before:bottom-4 before:left-[1.35rem] before:w-px before:bg-slate-300">
                        @foreach ($package->days as $day)
                            <li class="relative pl-14">
                                <span class="absolute top-3 left-0 grid size-11 place-items-center rounded-full bg-brand-800 text-lg font-bold tracking-tight text-white ring-4 ring-white">{{ $day->day_number }}</span>
                                <div class="rounded-2xl bg-white ring-1 ring-slate-200">
                                    <button type="button" class="flex w-full items-center justify-between gap-4 p-5 text-left"
                                            @click="open.includes({{ $loop->index }}) ? open = open.filter(i => i !== {{ $loop->index }}) : open.push({{ $loop->index }})"
                                            :aria-expanded="open.includes({{ $loop->index }})">
                                        <span>
                                            <span class="block text-xs font-medium text-brand-600">Day {{ $day->day_number }}</span>
                                            <span class="block text-lg font-semibold text-slate-900">{{ $day->title }}</span>
                                        </span>
                                        <x-icon name="chevron-down" class="size-5 shrink-0 text-ink/40 transition" ::class="open.includes({{ $loop->index }}) && 'rotate-180'"/>
                                    </button>
                                    <div x-show="open.includes({{ $loop->index }})" x-collapse @if(! $loop->first) x-cloak @endif>
                                        <div class="border-t border-slate-200 px-5 pt-4 pb-5">
                                            <p class="leading-relaxed text-ink/75">{{ $day->description }}</p>
                                            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                                @if ($day->hotel)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 font-medium text-brand-800"><x-icon name="bed" class="size-4"/>{{ $day->hotel->name }}</span>
                                                @endif
                                                @if ($day->meals)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-lake-50 px-3 py-1.5 font-medium text-lake-700"><x-icon name="utensils" class="size-4"/>{{ $day->meals }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Stays --}}
            @if ($stays->isNotEmpty())
                <section>
                    <h2 class="heading-md text-slate-900">Where you’ll stay</h2>
                    <p class="mt-2 text-ink/60">Hand-picked hotels we have stayed in ourselves. Upgrades are available on request.</p>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($stays as $hotel)
                            @php $nights = $package->days->where('hotel_id', $hotel->id)->count(); @endphp
                            <article class="flex min-w-0 gap-4 rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                                <x-media :src="$hotel->image_url" :alt="$hotel->name" class="size-24 shrink-0 rounded-xl sm:size-28"/>
                                <div class="min-w-0 py-1">
                                    <p class="text-xs text-brand-600">{{ $hotel->type }} · {{ $hotel->destination?->name }}</p>
                                    <h3 class="truncate font-semibold text-slate-900">{{ $hotel->name }}</h3>
                                    <div class="mt-1 flex text-slate-900" aria-label="{{ $hotel->stars }} stars">@for ($i = 0; $i < $hotel->stars; $i++)<x-icon name="star" class="size-3.5"/>@endfor</div>
                                    <p class="mt-1 text-xs text-ink/60">{{ $nights }} {{ Str::plural('night', $nights) }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Inclusions --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl bg-brand-50 p-6 sm:p-8">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Included</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($package->inclusions ?? [] as $item)
                            <li class="flex gap-3 text-ink/80"><x-icon name="check" class="mt-0.5 size-4.5 shrink-0 text-brand-600"/>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200 sm:p-8">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Not included</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($package->exclusions ?? [] as $item)
                            <li class="flex gap-3 text-ink/70"><x-icon name="x" class="mt-0.5 size-4.5 shrink-0 text-rose-500"/>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Gallery --}}
            @if (count($package->gallery_urls))
                <section x-data="{ lightbox: null, images: @js($package->gallery_urls) }">
                    <h2 class="heading-md text-slate-900">Gallery</h2>
                    @php $feature = count($package->gallery_urls) % 3 === 0; @endphp
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($package->gallery_urls as $url)
                            <button type="button" @click="lightbox = {{ $loop->index }}" @class(['group overflow-hidden rounded-2xl', 'col-span-2 row-span-2' => $feature && $loop->first])>
                                <x-media :src="$url" alt="" class="size-full min-h-32 {{ $feature && $loop->first ? 'aspect-square' : 'aspect-[4/3]' }}" img-class="transition duration-500 group-hover:scale-105"/>
                            </button>
                        @endforeach
                    </div>
                    <div x-cloak x-show="lightbox !== null" x-transition.opacity @click.self="lightbox = null" @keydown.escape.window="lightbox = null"
                         @keydown.arrow-right.window="lightbox !== null && (lightbox = (lightbox + 1) % images.length)"
                         @keydown.arrow-left.window="lightbox !== null && (lightbox = (lightbox - 1 + images.length) % images.length)"
                         class="fixed inset-0 z-[60] grid place-items-center bg-slate-950/95 p-4">
                        <img :src="images[lightbox]" alt="" class="max-h-[85vh] max-w-full rounded-2xl object-contain">
                        <button type="button" @click="lightbox = null" class="absolute top-4 right-4 grid size-11 place-items-center rounded-full bg-white/10 text-white" aria-label="Close"><x-icon name="x"/></button>
                    </div>
                </section>
            @endif

            {{-- Reviews --}}
            @if ($package->testimonials->isNotEmpty())
                <section>
                    <h2 class="heading-md text-slate-900">What guests say</h2>
                    <div class="mt-6 grid gap-4">
                        @foreach ($package->testimonials as $t)
                            <figure class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 sm:p-8">
                                <div class="flex text-slate-900">@for ($i = 0; $i < $t->rating; $i++)<x-icon name="star" class="size-4"/>@endfor</div>
                                <blockquote class="mt-3 leading-relaxed text-slate-700">{{ $t->quote }}</blockquote>
                                <figcaption class="mt-4 text-sm text-ink/60"><span class="font-semibold text-ink">{{ $t->name }}</span> · {{ $t->location }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Booking panel --}}
        <aside class="min-w-0 lg:col-span-5 xl:col-span-4" id="book">
            <div class="rounded-2xl bg-white p-6 shadow-[0_24px_60px_-30px_rgb(15_23_42/0.35)] ring-1 ring-slate-200 sm:p-7 lg:sticky lg:top-24">
                <p class="text-xs font-medium text-slate-500">From</p>
                <x-price :amount="$package->price" :original="$package->original_price" size="lg"/>

                <dl class="mt-5 space-y-2.5 border-t border-slate-100 pt-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Adult (12+)</dt><dd class="font-semibold text-slate-900">{{ money($package->price) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Child (2–11)</dt><dd class="font-semibold text-slate-900">{{ money($pricing->childPrice($package, 1)) }}</dd></div>
                    @foreach ($pricing->tiers($package) as $tier)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Group of {{ $tier['min'] }}+</dt><dd class="font-semibold text-brand-700">{{ money($tier['price']) }} per adult</dd></div>
                    @endforeach
                </dl>

                @if ($rooms->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-sm font-semibold text-slate-900">Room options</p>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            @foreach ($rooms as $room)
                                <li class="flex justify-between gap-4">
                                    <span class="text-slate-600">{{ $room['name'] }}</span>
                                    <span class="shrink-0 font-medium {{ $room['adjustment'] < 0 ? 'text-brand-700' : 'text-slate-900' }}">{{ $room['adjustment'] == 0 ? 'Included' : ($room['adjustment'] > 0 ? '+ ' : '− ').money(abs($room['adjustment'])) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <a href="{{ route('packages.book', $package) }}" class="btn btn-primary mt-6 w-full py-4 text-base">Choose dates & options</a>
                <p class="mt-3 text-center text-xs leading-relaxed text-slate-500">No payment now. We confirm availability within 24 hours.</p>
            </div>
            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-brand-50 p-4 text-sm text-brand-900">
                <x-icon name="phone" class="size-5 shrink-0 text-brand-600"/>
                <p>Prefer to talk? Call our Toronto desk on <span class="font-semibold whitespace-nowrap">{{ setting('contact_phone_ca') }}</span></p>
            </div>
        </aside>
    </div>

    {{-- Related --}}
    @if ($related->isNotEmpty())
        <section class="bg-slate-100 py-20">
            <div class="container-x">
                <x-section-heading title="You may also like"/>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $p)
                        <x-package-card :package="$p"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Mobile booking bar --}}
    <div id="mobile-book-bar" class="fixed inset-x-0 bottom-0 z-30 flex items-center justify-between gap-4 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
        <div>
            <p class="text-[0.7rem] text-ink/50">From, per person</p>
            <p class="text-2xl font-bold tracking-tight text-slate-900">{{ money($package->price) }}</p>
        </div>
        <a href="{{ route('packages.book', $package) }}" class="btn btn-primary">Book now</a>
    </div>
    <div class="h-20 lg:hidden"></div>
</x-site-layout>
