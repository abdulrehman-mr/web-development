<section class="px-4 pb-16 sm:px-6 sm:pb-24 lg:px-8">
    <div class="relative isolate mx-auto max-w-7xl overflow-hidden rounded-3xl px-6 py-16 text-center text-white sm:px-12 sm:py-24">
        <x-media :src="media_url(setting('about_image'))" alt="" class="absolute! inset-0 -z-10"/>
        <div class="absolute inset-0 -z-10 bg-brand-950/75"></div>
        <h2 class="heading-lg mx-auto max-w-3xl text-balance">Ready to plan your journey to Pakistan?</h2>
        <p class="mx-auto mt-4 max-w-xl text-white/80">Tell us when you would like to travel and who is coming. We will send a tailored itinerary and price within one working day, with no obligation.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('contact') }}" class="btn btn-light">Start planning</a>
            <a href="{{ route('packages.index') }}" class="btn btn-ghost-light">Browse journeys</a>
        </div>
    </div>
</section>
