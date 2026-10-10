<x-site-layout title="About us">
    <x-page-hero eyebrow="About us" :title="setting('about_title')" :image="media_url(setting('about_image'))"/>

    <section class="py-16 sm:py-24">
        <div class="container-x grid gap-12 lg:grid-cols-12 lg:gap-20">
            <div class="prose-travel text-lg lg:col-span-7">
                @foreach (preg_split('/\n\s*\n/', (string) setting('about_body')) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
            <dl class="grid grid-cols-3 gap-6 self-start lg:col-span-5 lg:grid-cols-1 lg:gap-8">
                @foreach (['stat_1', 'stat_2', 'stat_3'] as $key)
                    @php [$value, $label] = array_pad(explode('|', (string) setting($key)), 2, ''); @endphp
                    @if ($value)
                        <div class="border-l-2 border-lake-300 pl-4 lg:pl-6">
                            <dd class="text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">{{ $value }}</dd>
                            <dt class="mt-1 text-xs text-ink/60 sm:text-sm">{{ $label }}</dt>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>
    </section>

    <section class="bg-brand-950 py-16 text-white sm:py-24">
        <div class="container-x">
            <x-section-heading title="What every journey includes" light/>
            <div class="mt-12 grid gap-px overflow-hidden rounded-3xl bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['plane', 'Airport welcome', 'Met at arrivals with garlands and driven to your hotel — no queues for taxis.'],
                    ['document', 'Visa support', 'An invitation letter and step-by-step help with Pakistan’s e-visa.'],
                    ['shield', 'Licensed guides', 'Government-licensed guides who speak English, Urdu and Punjabi.'],
                    ['users', 'Always private', 'Your own vehicle, your own guide, your own pace.'],
                    ['bed', 'Tested stays', 'Every hotel, haveli and camp has been personally inspected.'],
                    ['phone', '24/7 support', 'One WhatsApp line to our Lahore and Toronto teams, day or night.'],
                ] as [$icon, $title, $text])
                    <div class="bg-brand-950 p-8">
                        <x-icon :name="$icon" class="size-7 text-lake-300"/>
                        <h3 class="mt-5 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/65">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="py-16 sm:py-24">
            <div class="container-x grid gap-6 md:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="rounded-2xl bg-white p-6 ring-1 ring-slate-200" data-reveal>
                        <x-icon name="quote" class="size-8 text-lake-300"/>
                        <blockquote class="mt-4 leading-relaxed text-ink/80">{{ $t->quote }}</blockquote>
                        <figcaption class="mt-6 text-sm"><span class="font-semibold">{{ $t->name }}</span><span class="text-ink/50"> · {{ $t->location }}</span></figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.cta')
</x-site-layout>
