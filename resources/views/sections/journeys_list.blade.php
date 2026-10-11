<section id="section-{{ $section->id }}" class="py-12 sm:py-16">
    <div class="container-x">
        {{-- Filters --}}
        <form method="get" x-data @change="$el.submit()" class="grid gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-200 sm:grid-cols-2 {{ count($countries) > 1 ? 'lg:grid-cols-6' : 'lg:grid-cols-5' }}">
            @if (! empty($filters['vibe']))<input type="hidden" name="vibe" value="{{ $filters['vibe'] }}">@endif
            @if (count($countries) > 1)
                <select name="country" class="field border-transparent bg-white" aria-label="Country">
                    <option value="">All countries</option>
                    @foreach ($countries as $code => $countryName)
                        <option value="{{ $code }}" @selected(strtoupper($filters['country'] ?? '') === $code)>{{ $countryName }}</option>
                    @endforeach
                </select>
            @endif
            <label class="relative">
                <span class="sr-only">Search</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-4.5 -translate-y-1/2 text-ink/40"/>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search journeys" class="field border-transparent bg-white pl-11" @change.stop>
            </label>
            <select name="category" class="field border-transparent bg-white" aria-label="Experience">
                <option value="">All experiences</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->slug }}" @selected(($filters['category'] ?? '') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="destination" class="field border-transparent bg-white" aria-label="Destination">
                <option value="">All destinations</option>
                @foreach ($destinations as $d)
                    <option value="{{ $d->slug }}" @selected(($filters['destination'] ?? '') === $d->slug)>{{ $d->name }}</option>
                @endforeach
            </select>
            <select name="duration" class="field border-transparent bg-white" aria-label="Trip length">
                <option value="">Any length</option>
                <option value="short" @selected(($filters['duration'] ?? '') === 'short')>Up to 6 days</option>
                <option value="medium" @selected(($filters['duration'] ?? '') === 'medium')>7 – 9 days</option>
                <option value="long" @selected(($filters['duration'] ?? '') === 'long')>10+ days</option>
            </select>
            <select name="sort" class="field border-transparent bg-white" aria-label="Sort by">
                <option value="featured" @selected(($filters['sort'] ?? 'featured') === 'featured')>Recommended</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: low to high</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: high to low</option>
                <option value="duration" @selected(($filters['sort'] ?? '') === 'duration')>Shortest first</option>
            </select>
        </form>

        @if ($s['show_vibes'] && $vibes->isNotEmpty())
            <div class="mt-4 flex gap-2 overflow-x-auto pb-1" aria-label="Filter by vibe">
                <a href="{{ route('packages.index', \Illuminate\Support\Arr::except(request()->query(), ['vibe', 'page'])) }}" @class(['inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-sm font-medium ring-1 transition', empty($filters['vibe']) ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'])>All vibes</a>
                @foreach ($vibes as $vibe)
                    <a href="{{ route('packages.index', [...\Illuminate\Support\Arr::except(request()->query(), ['vibe', 'page']), 'vibe' => $vibe->slug]) }}" @class(['inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-sm font-medium ring-1 transition', ($filters['vibe'] ?? '') === $vibe->slug ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'])>
                        <x-icon :name="$vibe->icon" class="size-4"/>{{ $vibe->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-8 flex items-center justify-between text-sm text-ink/60">
            <p>{{ $packages->total() }} {{ Str::plural('journey', $packages->total()) }}</p>
            @if (array_filter($filters))
                <a href="{{ route('packages.index') }}" class="font-semibold text-brand-700 hover:underline">Clear filters</a>
            @endif
        </div>

        @if ($packages->isEmpty())
            <div class="mt-8 rounded-3xl bg-slate-100 p-12 text-center">
                <p class="text-2xl font-bold tracking-tight text-slate-900">{{ $s['empty_title'] }}</p>
                <p class="mt-2 text-ink/60">{{ $s['empty_text'] }}</p>
                <a href="{{ route('contact') }}" class="btn btn-primary mt-6">{{ $s['empty_button'] ?: 'Contact us' }}</a>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    <x-package-card :package="$package"/>
                @endforeach
            </div>
            <div class="mt-12">{{ $packages->links() }}</div>
        @endif
    </div>
</section>
