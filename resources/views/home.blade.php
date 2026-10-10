<x-site-layout>
    {{-- ── Hero: clean white, with the Mughal arch as the one signature shape ── --}}
    <section class="pt-16 lg:pt-[4.5rem]">
        <div class="container-x grid items-center gap-10 py-10 sm:py-14 lg:grid-cols-12 lg:gap-14 lg:py-20">
            <div class="lg:col-span-7">
                @if (setting('hero_eyebrow'))
                    <p class="mb-6 inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-800 ring-1 ring-brand-100">
                        <x-icon name="plane" class="size-4"/>{{ setting('hero_eyebrow') }}
                    </p>
                @endif
                <h1 class="heading-xl text-balance text-slate-900">{{ setting('hero_title') }}</h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">{{ setting('hero_subtitle') }}</p>

                {{-- Journey finder --}}
                <form action="{{ route('packages.index') }}" method="get" class="mt-9 grid gap-1 rounded-2xl bg-white p-2 shadow-[0_12px_40px_-12px_rgb(15_23_42/0.25)] ring-1 ring-slate-200 sm:grid-cols-[1fr_1fr_auto] sm:items-center">
                    <label class="rounded-xl px-4 py-2.5 transition hover:bg-slate-50 sm:border-r sm:border-slate-200 sm:rounded-r-none">
                        <span class="block text-xs font-semibold text-slate-900">Experience</span>
                        <select name="category" class="-ml-1 w-full cursor-pointer appearance-none bg-transparent text-sm text-slate-600 outline-none">
                            <option value="">Any experience</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="rounded-xl px-4 py-2.5 transition hover:bg-slate-50">
                        <span class="block text-xs font-semibold text-slate-900">Trip length</span>
                        <select name="duration" class="-ml-1 w-full cursor-pointer appearance-none bg-transparent text-sm text-slate-600 outline-none">
                            <option value="">Any length</option>
                            <option value="short">Up to 6 days</option>
                            <option value="medium">7 – 9 days</option>
                            <option value="long">10 days or more</option>
                        </select>
                    </label>
                    <button class="btn btn-primary py-3.5"><x-icon name="search" class="size-4.5"/> Find journeys</button>
                </form>

                <ul class="mt-8 grid grid-cols-2 gap-x-6 gap-y-3 text-sm text-slate-600 sm:flex sm:flex-wrap">
                    <li class="flex items-center gap-2"><x-icon name="plane" class="size-4.5 text-brand-600"/>Airport welcome</li>
                    <li class="flex items-center gap-2"><x-icon name="document" class="size-4.5 text-brand-600"/>E-visa letter</li>
                    <li class="flex items-center gap-2"><x-icon name="shield" class="size-4.5 text-brand-600"/>Licensed guides</li>
                    <li class="flex items-center gap-2"><x-icon name="phone" class="size-4.5 text-brand-600"/>Toronto & Lahore desks</li>
                </ul>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto w-full max-w-[19rem] sm:max-w-sm lg:max-w-none">
                    <x-media :src="media_url(setting('hero_image'))" alt="Badshahi Mosque, Lahore" eager class="arch aspect-[4/5]"/>
                    @if ($yatra)
                        <a href="{{ route('packages.show', $yatra) }}" class="absolute -bottom-5 -left-3 flex max-w-[16rem] items-center gap-3 rounded-2xl bg-white p-3 pr-4 shadow-xl ring-1 ring-slate-200 transition hover:ring-brand-300 sm:-left-8">
                            <x-media :src="$yatra->hero_image_url" class="size-12 shrink-0 rounded-xl"/>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $yatra->category?->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $yatra->duration_days }} days from {{ money($yatra->price) }}</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ── Experiences: mosaic, first tile larger ── --}}
    <section class="py-16 sm:py-24">
        <div class="container-x">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <x-section-heading title="Six ways to discover Pakistan" intro="Every journey is private and can be tailored around your dates, pace and family."/>
                <a href="{{ route('packages.index') }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-800 hover:underline">See all journeys</a>
            </div>

            <div class="mt-10 grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] lg:grid-cols-3 lg:gap-4">
                @foreach ($categories as $category)
                    <a href="{{ route('packages.index', ['category' => $category->slug]) }}" @class(['group relative isolate block overflow-hidden rounded-2xl', 'col-span-2 row-span-2' => $loop->first, 'col-span-2 lg:col-span-1' => $loop->last && $loop->count % 2 === 0]) data-reveal>
                        <x-media :src="$category->image_url" :alt="$category->name" class="absolute! inset-0 -z-10" img-class="transition duration-700 group-hover:scale-105"/>
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 text-white sm:p-5">
                            <h3 @class(['font-bold tracking-tight', $loop->first ? 'text-2xl sm:text-3xl' : 'text-base sm:text-lg'])>{{ $category->name }}</h3>
                            <p @class(['mt-0.5 text-white/80', $loop->first ? 'text-sm sm:text-base' : 'hidden text-sm sm:block'])>{{ $category->tagline }}</p>
                            <p class="mt-1 text-xs text-white/70">{{ $category->packages_count }} {{ Str::plural('journey', $category->packages_count) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Featured journeys ── --}}
    <section class="pb-16 sm:pb-24">
        <div class="container-x">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <x-section-heading title="Most-loved journeys" intro="Prices include private transport, licensed guides, hand-picked stays and every entrance fee."/>
                <a href="{{ route('packages.index') }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-800 hover:underline">Browse all</a>
            </div>
            <div class="mt-10 grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($featured as $package)
                    <x-package-card :package="$package"/>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Sikh Yatra spotlight (flag green) ── --}}
    @if ($yatra)
        <section class="bg-brand-900 text-white">
            <div class="container-x grid items-center gap-10 py-16 sm:py-24 lg:grid-cols-2 lg:gap-16">
                <x-media :src="$yatra->hero_image_url" :alt="$yatra->title" class="aspect-[4/3] rounded-3xl lg:aspect-[5/4]" data-reveal/>
                <div data-reveal>
                    <p class="mb-3 text-sm font-semibold text-lake-300">For the sangat</p>
                    <h2 class="heading-lg text-balance">{{ $yatra->title }}</h2>
                    <p class="mt-4 text-lg leading-relaxed text-white/80">{{ $yatra->summary }}</p>
                    <ul class="mt-7 space-y-3">
                        @foreach (array_slice($yatra->highlights ?? [], 0, 5) as $highlight)
                            <li class="flex gap-3 text-white/90"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-lake-300"/>{{ $highlight }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-5">
                        <x-price :amount="$yatra->price" :original="$yatra->original_price" light/>
                        <a href="{{ route('packages.show', $yatra) }}" class="btn btn-light">See the full yatra</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── Destinations: arch tiles ── --}}
    <section class="py-16 sm:py-24">
        <div class="container-x">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <x-section-heading title="From the Arabian Sea to the Karakoram"/>
                <a href="{{ route('destinations.index') }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-800 hover:underline">All destinations</a>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-3 lg:gap-x-8">
                @foreach ($destinations as $destination)
                    <a href="{{ route('destinations.show', $destination) }}" class="group block" data-reveal>
                        <x-media :src="$destination->image_url" :alt="$destination->name" class="arch aspect-[3/4]" img-class="transition duration-700 group-hover:scale-105"/>
                        <h3 class="mt-4 text-lg font-bold tracking-tight text-slate-900 sm:text-xl">{{ $destination->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $destination->region }} · {{ $destination->tagline }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Story & numbers ── --}}
    <section class="bg-slate-50 py-16 sm:py-24">
        <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <x-media :src="media_url(setting('story_image'))" alt="" class="aspect-[4/3] rounded-3xl" data-reveal/>
            <div data-reveal>
                <h2 class="heading-lg text-balance text-slate-900">{{ setting('story_title') }}</h2>
                <p class="mt-5 text-lg leading-relaxed text-slate-600">{{ setting('story_body') }}</p>
                <dl class="mt-10 grid grid-cols-3 gap-6">
                    @foreach (['stat_1', 'stat_2', 'stat_3'] as $key)
                        @php [$value, $label] = array_pad(explode('|', (string) setting($key)), 2, ''); @endphp
                        @if ($value)
                            <div class="border-t-2 border-brand-600 pt-4">
                                <dd class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $value }}</dd>
                                <dt class="mt-1 text-xs leading-snug text-slate-500 sm:text-sm">{{ $label }}</dt>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ── How it works (numbered because it is a sequence) ── --}}
    <section class="py-16 sm:py-24">
        <div class="container-x">
            <x-section-heading title="From first message to farewell dinner" center/>
            <ol class="relative mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8 lg:before:absolute lg:before:top-5 lg:before:right-[12%] lg:before:left-[12%] lg:before:h-px lg:before:bg-slate-200">
                @foreach ([
                    ['Tell us your plans', 'Pick a journey or describe your dream trip. A travel designer replies within one working day.'],
                    ['Shape your itinerary', 'We tailor dates, hotels and pace, including visits to family villages or gurdwaras.'],
                    ['Secure with a deposit', 'A 25% deposit holds your dates. We send your e-visa invitation letter straight away.'],
                    ['Land and relax', 'We meet you at arrivals and stay with you until your flight home.'],
                ] as $i => [$title, $text])
                    <li class="relative lg:text-center" data-reveal>
                        <span class="relative grid size-10 place-items-center rounded-full bg-brand-700 text-sm font-bold text-white ring-8 ring-white lg:mx-auto">{{ $i + 1 }}</span>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ── Reviews ── --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-slate-50 py-16 sm:py-24">
            <div class="container-x">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <x-section-heading title="What our guests say"/>
                    <p class="flex items-center gap-2 text-sm text-slate-600"><x-icon name="star" class="size-4 text-slate-900"/><span class="font-semibold text-slate-900">{{ explode('|', setting('stat_3', '4.9/5|'))[0] }}</span> average from {{ explode('|', setting('stat_1', '1,200+|'))[0] }} guests</p>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach ($testimonials->take(3) as $t)
                        <figure class="flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200" data-reveal>
                            <div class="flex gap-0.5 text-slate-900" aria-label="{{ $t->rating }} out of 5">@for ($i = 0; $i < $t->rating; $i++)<x-icon name="star" class="size-4"/>@endfor</div>
                            <blockquote class="mt-4 flex-1 leading-relaxed text-slate-700">{{ $t->quote }}</blockquote>
                            <figcaption class="mt-6 border-t border-slate-100 pt-4 text-sm">
                                <span class="block font-semibold text-slate-900">{{ $t->name }}</span>
                                <span class="text-slate-500">{{ $t->location }}@if ($t->package) · {{ $t->package->title }}@endif</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── FAQ ── --}}
    @if ($faqs->isNotEmpty())
        <section class="py-16 sm:py-24">
            <div class="container-x grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <x-section-heading title="Questions we hear most" intro="Can’t find your answer? Our Toronto desk is happy to help."/>
                    <a href="{{ route('contact') }}" class="btn btn-ghost mt-6">Ask us anything</a>
                </div>
                <div class="divide-y divide-slate-200 border-y border-slate-200 lg:col-span-8" x-data="{ open: 0 }">
                    @foreach ($faqs as $faq)
                        <div>
                            <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="flex w-full items-center justify-between gap-6 py-5 text-left" :aria-expanded="open === {{ $loop->index }}">
                                <span class="font-semibold text-slate-900">{{ $faq->question }}</span>
                                <x-icon name="chevron-down" class="size-5 shrink-0 text-slate-400 transition" ::class="open === {{ $loop->index }} && 'rotate-180'"/>
                            </button>
                            <div x-show="open === {{ $loop->index }}" x-collapse @if(! $loop->first) x-cloak @endif>
                                <p class="max-w-2xl pb-5 leading-relaxed text-slate-600">{{ $faq->answer }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta')
</x-site-layout>
