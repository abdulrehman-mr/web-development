<x-builder.section :section="$section">
    <div class="container-x space-y-16">
        @if ($s['title'])<h2 class="heading-lg text-slate-900">{{ $s['title'] }}</h2>@endif
        @foreach ($hotels as $type => $group)
            <div>
                <h2 class="heading-md border-b border-slate-300 pb-4 text-slate-900">{{ Str::plural($type) }}</h2>
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    @foreach ($group as $hotel)
                        @include('partials.hotel-card')
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-builder.section>
