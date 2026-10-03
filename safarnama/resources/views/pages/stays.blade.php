<x-site-layout title="Where you’ll stay">
    <x-page-hero eyebrow="Stays" title="Where you’ll stay" intro="Restored havelis, lakeside lodges, private desert camps and the best five-star hotels in each city. We have slept in every one of them." :image="media_url(setting('story_image'))"/>

    <div class="container-x space-y-16 py-16 sm:py-24">
        @foreach ($hotels as $type => $group)
            <section>
                <h2 class="heading-md border-b border-marble-300 pb-4 text-pine-950">{{ Str::plural($type) }}</h2>
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    @foreach ($group as $hotel)
                        @include('partials.hotel-card')
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    @include('partials.cta')
</x-site-layout>
