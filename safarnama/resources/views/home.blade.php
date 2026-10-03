<x-site-layout>
    {{-- ── Hero: the one bold moment — a Mughal arch framing the photograph ── --}}
    <section class="relative isolate overflow-hidden bg-pine-950 text-marble-50">
        <div class="jali-bg absolute inset-0 -z-10 opacity-70"></div>
        <div class="absolute -top-40 -right-40 -z-10 size-[36rem] rounded-full bg-brass-500/15 blur-3xl"></div>

        <div class="container-x grid items-end gap-10 pt-28 pb-14 sm:pt-32 lg:min-h-[92vh] lg:grid-cols-12 lg:gap-12 lg:pt-36 lg:pb-20">
            <div class="order-2 lg:order-1 lg:col-span-7 lg:pb-10">
                <p class="eyebrow mb-5 text-brass-300">{{ setting('hero_eyebrow') }}</p>
                <h1 class="heading-xl text-balance">{{ setting('hero_title') }}</h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-marble-50/75 sm:text-lg">{{ setting('hero_subtitle') }}</p>

                {{-- Journey finder --}}
                <form action="{{ route('packages.index') }}" method="get" class="mt-9 grid gap-2 rounded-3xl bg-marble-50 p-2 text-ink shadow-2xl shadow-pine-950/40 sm:grid-cols-[1fr_1fr_auto]">
                    <label class="rounded-2xl px-4 py-2.5 transition focus-within:bg-marble-100 hover:bg-marble-100">
                        <span class="block text-[0.7rem] font-semibold text-ink/50">Experience</span>
                        <select name="category" class="-ml-1 w-full cursor-pointer appearance-none bg-transparent text-sm font-semibold text-pine-950 outline-none">
                            <option value="">Any experience</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="rounded-2xl px-4 py-2.5 transition focus-within:bg-marble-100 hover:bg-marble-100">
                        <span class="block text-[0.7rem] font-semibold text-ink/50">Trip length</span>
                        <select name="duration" class="-ml-1 w-full cursor-pointer appearance-none bg-transparent text-sm font-semibold text-pine-950 outline-none">
                            <option value="">Any length</option>
                            <option value="short">Up to 6 days</option>
                            <option value="medium">7 – 9 days</option>
                            <option value="long">10 days or more</option>
                        </select>
                    </label>
                    <button class="btn btn-primary rounded-2xl px-7 py-4"><x-icon name="search" class="size-4.5"/> Find journeys</button>
                </form>

                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-marble-50/70">
                    <li class="inline-flex items-center gap-2"><x-icon name="plane" class="size-4.5 text-brass-400"/>Airport welcome</li>
                    <li class="inline-flex items-center gap-2"><x-icon name="document" class="size-4.5 text-brass-400"/>E-visa invitation letter</li>
                    <li class="inline-flex items-center gap-2"><x-icon name="shield" class="size-4.5 text-brass-400"/>Licensed guides</li>
                    <li class="inline-flex items-center gap-2"><x-icon name="phone" class="size-4.5 text-brass-400"/>Toronto & Lahore desks</li>
                </ul>
            </div>

            <div class="order-1 lg:order-2 lg:col-span-5">
                <div class="relative mx-auto w-full max-w-[17rem] sm:max-w-md lg:max-w-none">
                    <div class="arch absolute -inset-2 bg-brass-400/40 sm:-inset-3"></div>
                    <x-media :src="media_url(setting('hero_image'))" alt="Badshahi Mosque, Lahore" eager class="arch aspect-[5/6] lg:aspect-[4/5]"/>
                    <div class="absolute -bottom-4 -left-2 rounded-2xl bg-marble-50 px-4 py-3 text-ink shadow-xl sm:-left-6">
                        <div class="flex items-center gap-1 text-brass-500">
                            @for ($i = 0; $i < 5; $i++)<x-icon name="star" class="size-3.5"/>@endfor
                        </div>
                        <p class="mt-1 text-xs font-semibold">Rated {{ explode('|', setting('stat_3', '4.9/5|'))[0] }} by our guests</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Experiences ── --}}
    <section class="py-20 sm:py-28">
        <div class="container-x">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <x-section-heading eyebrow="Ways to travel" title="Six ways to discover Pakistan" intro="Every journey is private and can be tailored. Start with the experience that draws you, and we will shape the rest around you."/>
                <a href="{{ route('packages.index') }}" class="btn btn-ghost self-start md:self-auto">All journeys <x-icon name="arrow-right" class="size-4"/></a>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('packages.index', ['category' => $category->slug]) }}" class="group relative isolate block overflow-hidden rounded-3xl" data-reveal>
                        <x-media :src="$category->image_url" :alt="$category->name" class="aspect-[4/3] sm:aspect-[5/4]" img-class="transition duration-700 group-hover:scale-105"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-pine-950/90 via-pine-950/30 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 text-marble-50">
                            <p class="text-xs font-medium text-brass-300">{{ $category->packages_count }} {{ Str::plural('journey', $category->packages_count) }}</p>
                            <h3 class="mt-1 font-display text-3xl font-semibold">{{ $category->name }}</h3>
                            <p class="mt-1 text-sm text-marble-50/75">{{ $category->tagline }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Featured journeys ── --}}
    <section class="bg-marble-100 py-20 sm:py-28">
        <div class="container-x">
            <x-section-heading eyebrow="Signature journeys" title="Our most-loved itineraries" intro="Prices include private transport, licensed guides, hand-picked stays and every entrance fee."/>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($featured as $package)
                    <x-package-card :package="$package"/>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Sikh Yatra spotlight ── --}}
    @if ($yatra)
        <section class="relative isolate overflow-hidden bg-pine-900 text-marble-50">
            <div class="jali-bg absolute inset-0 -z-10"></div>
            <div class="container-x grid items-center gap-12 py-20 sm:py-28 lg:grid-cols-2">
                <div class="relative mx-auto w-full max-w-md lg:max-w-none" data-reveal>
                    <x-media :src="$yatra->hero_image_url" :alt="$yatra->title" class="arch aspect-[4/5]"/>
                </div>
                <div data-reveal>
                    <p class="eyebrow mb-4 text-brass-300">For the sangat</p>
                    <h2 class="heading-lg">{{ $yatra->title }}</h2>
                    <p class="mt-5 text-lg leading-relaxed text-marble-50/75">{{ $yatra->summary }}</p>
                    <ul class="mt-8 space-y-3">
                        @foreach (array_slice($yatra->highlights ?? [], 0, 5) as $highlight)
                            <li class="flex gap-3 text-marble-50/85"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-brass-400"/>{{ $highlight }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-10 flex flex-wrap items-center gap-6">
                        <x-price :amount="$yatra->price" :original="$yatra->original_price" light/>
                        <a href="{{ route('packages.show', $yatra) }}" class="btn btn-brass">See the full yatra <x-icon name="arrow-right" class="size-4"/></a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── Destinations ── --}}
    <section class="py-20 sm:py-28">
        <div class="container-x">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <x-section-heading eyebrow="Destinations" title="From the Arabian Sea to the Karakoram"/>
                <a href="{{ route('destinations.index') }}" class="btn btn-ghost self-start md:self-auto">All destinations <x-icon name="arrow-right" class="size-4"/></a>
            </div>
            <div class="mt-12 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:gap-x-8">
                @foreach ($destinations as $destination)
                    <a href="{{ route('destinations.show', $destination) }}" class="group block" data-reveal>
                        <x-media :src="$destination->image_url" :alt="$destination->name" class="arch aspect-[3/4]" img-class="transition duration-700 group-hover:scale-105"/>
                        <p class="mt-4 text-xs font-medium text-brass-600">{{ $destination->region }}</p>
                        <h3 class="font-display text-2xl font-semibold text-pine-950 sm:text-3xl">{{ $destination->name }}</h3>
                        <p class="mt-1 text-sm text-ink/60">{{ $destination->tagline }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Story & numbers ── --}}
    <section class="bg-marble-100 py-20 sm:py-28">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <x-media :src="media_url(setting('story_image'))" alt="" class="aspect-[4/3] rounded-3xl lg:aspect-square" data-reveal/>
            <div data-reveal>
                <p class="eyebrow mb-3">Why travel with us</p>
                <h2 class="heading-lg text-pine-950">{{ setting('story_title') }}</h2>
                <p class="mt-5 text-lg leading-relaxed text-ink/70">{{ setting('story_body') }}</p>
                <dl class="mt-10 grid grid-cols-3 gap-4 border-t border-marble-300 pt-8">
                    @foreach (['stat_1', 'stat_2', 'stat_3'] as $key)
                        @php [$value, $label] = array_pad(explode('|', (string) setting($key)), 2, ''); @endphp
                        @if ($value)
                            <div>
                                <dt class="sr-only">{{ $label }}</dt>
                                <dd class="font-display text-3xl font-semibold text-pine-900 sm:text-5xl">{{ $value }}</dd>
                                <dd class="mt-1 text-xs leading-snug text-ink/60 sm:text-sm">{{ $label }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ── How it works (numbered because it is a sequence) ── --}}
    <section class="py-20 sm:py-28">
        <div class="container-x">
            <x-section-heading eyebrow="How it works" title="From first message to farewell dinner" center/>
            <ol class="mt-14 grid gap-px overflow-hidden rounded-3xl bg-marble-200 ring-1 ring-marble-200 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Tell us your plans', 'Pick a journey or describe your dream trip. A travel designer replies within one working day.'],
                    ['Shape your itinerary', 'We tailor dates, hotels and pace — including visits to family villages or gurdwaras.'],
                    ['Secure with a deposit', 'A 25% deposit holds your dates. We send your e-visa invitation letter straight away.'],
                    ['Land and relax', 'We meet you at arrivals with garlands, and stay with you until your flight home.'],
                ] as $i => [$title, $text])
                    <li class="bg-marble-50 p-8" data-reveal>
                        <span class="font-display text-5xl font-semibold text-brass-500">{{ $i + 1 }}</span>
                        <h3 class="mt-4 text-lg font-semibold text-pine-950">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink/65">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ── Testimonials ── --}}
    @if ($testimonials->isNotEmpty())
        <section class="overflow-hidden bg-pine-950 py-20 text-marble-50 sm:py-28"
                 x-data="{ i: 0, n: {{ $testimonials->count() }} }" x-init="setInterval(() => i = (i + 1) % n, 8000)">
            <div class="container-x">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="eyebrow mb-8 text-brass-300">Guest stories</p>
                    <div class="relative grid">
                        @foreach ($testimonials as $t)
                            <figure x-show="i === {{ $loop->index }}" x-transition:enter="transition duration-700" x-transition:enter-start="opacity-0 translate-y-3" @if(! $loop->first) x-cloak @endif class="col-start-1 row-start-1">
                                <blockquote class="font-display text-2xl leading-snug font-medium text-balance sm:text-4xl">“{{ $t->quote }}”</blockquote>
                                <figcaption class="mt-8 text-sm">
                                    <span class="font-semibold text-marble-50">{{ $t->name }}</span>
                                    <span class="text-marble-50/60"> · {{ $t->location }}</span>
                                    @if ($t->package)
                                        <span class="mt-1 block text-brass-300">{{ $t->package->title }}</span>
                                    @endif
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="mt-10 flex justify-center gap-2">
                        @foreach ($testimonials as $t)
                            <button type="button" @click="i = {{ $loop->index }}" class="h-1.5 rounded-full transition-all" :class="i === {{ $loop->index }} ? 'w-8 bg-brass-400' : 'w-3 bg-marble-50/25'" aria-label="Show review {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── FAQ ── --}}
    @if ($faqs->isNotEmpty())
        <section class="py-20 sm:py-28">
            <div class="container-x grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <x-section-heading eyebrow="Good to know" title="Questions we hear most"/>
                    <a href="{{ route('contact') }}" class="btn btn-ghost mt-8">Ask us anything <x-icon name="arrow-right" class="size-4"/></a>
                </div>
                <div class="divide-y divide-marble-300 border-y border-marble-300 lg:col-span-8" x-data="{ open: 0 }">
                    @foreach ($faqs as $faq)
                        <div>
                            <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="flex w-full items-center justify-between gap-6 py-6 text-left" :aria-expanded="open === {{ $loop->index }}">
                                <span class="text-lg font-semibold text-pine-950">{{ $faq->question }}</span>
                                <span class="grid size-8 shrink-0 place-items-center rounded-full ring-1 ring-pine-900/15 transition" :class="open === {{ $loop->index }} && 'rotate-45 bg-pine-800 text-marble-50'"><x-icon name="plus" class="size-4"/></span>
                            </button>
                            <div x-show="open === {{ $loop->index }}" x-collapse @if(! $loop->first) x-cloak @endif>
                                <p class="max-w-2xl pb-6 leading-relaxed text-ink/70">{{ $faq->answer }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta')
</x-site-layout>
