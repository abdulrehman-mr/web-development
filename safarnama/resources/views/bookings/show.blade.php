<x-site-layout title="Booking received">
    <section class="pt-28 pb-20 sm:pt-32">
        <div class="container-x max-w-3xl">
            <div class="rounded-3xl bg-white p-8 text-center ring-1 ring-slate-200 sm:p-12">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-brand-50 text-brand-700"><x-icon name="check" class="size-8"/></span>
                <h1 class="heading-lg mt-6 text-slate-900">Thank you, {{ Str::before($booking->full_name, ' ') }}</h1>
                <p class="mx-auto mt-4 max-w-lg text-ink/70">We have received your request. A travel designer will confirm availability and send your tailored itinerary to <span class="font-semibold text-ink">{{ $booking->email }}</span> within 24 hours.</p>
                <p class="mt-8 inline-block rounded-full bg-slate-100 px-5 py-2 text-sm">Your reference: <span class="font-mono font-semibold tracking-wider text-slate-900">{{ $booking->reference }}</span></p>
            </div>

            <dl class="mt-6 grid gap-px overflow-hidden rounded-3xl bg-slate-200 ring-1 ring-slate-200 sm:grid-cols-2">
                @foreach ([
                    ['Journey', $booking->package?->title ?? '—'],
                    ['Start date', $booking->start_date?->format('j F Y')],
                    ['Travellers', $booking->adults.' '.Str::plural('adult', $booking->adults).($booking->children ? ', '.$booking->children.' '.Str::plural('child', $booking->children) : '')],
                    ['Estimated total', money($booking->total_estimate).' USD'],
                ] as [$label, $value])
                    <div class="bg-white p-6">
                        <dt class="text-xs font-semibold text-ink/50">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-10 rounded-3xl bg-brand-900 p-8 text-white">
                <h2 class="text-2xl font-bold tracking-tight">What happens next</h2>
                <ol class="mt-5 space-y-4 text-sm text-white/80">
                    <li class="flex gap-4"><span class="text-xl font-bold text-lake-300">1</span><span>We check hotel and guide availability for your dates and email a confirmed itinerary.</span></li>
                    <li class="flex gap-4"><span class="text-xl font-bold text-lake-300">2</span><span>A 25% deposit secures your journey. We then send your e-visa invitation letter.</span></li>
                    <li class="flex gap-4"><span class="text-xl font-bold text-lake-300">3</span><span>Two weeks before you fly, you receive your guide’s details and a WhatsApp group with our team.</span></li>
                </ol>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4"/> Back to home</a>
            </div>
        </div>
    </section>
</x-site-layout>
