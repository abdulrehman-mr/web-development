<x-site-layout title="Contact">
    <section class="pt-28 pb-16 sm:pt-32 sm:pb-24">
        <div class="container-x grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5">
                                <h1 class="heading-xl text-slate-900">Let’s plan your journey</h1>
                <p class="mt-5 text-lg leading-relaxed text-ink/70">Tell us when you would like to travel, who is coming and what you hope to see. A travel designer will reply within one working day.</p>

                <dl class="mt-10 space-y-6">
                    @foreach ([
                        ['phone', 'Canada', setting('contact_phone_ca')],
                        ['phone', 'Pakistan', setting('contact_phone')],
                        ['mail', 'Email', setting('contact_email')],
                        ['map-pin', 'Office', setting('contact_address')],
                        ['clock', 'Hours', setting('office_hours')],
                    ] as [$icon, $label, $value])
                        @if ($value)
                            <div class="flex gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700"><x-icon :name="$icon" class="size-5"/></span>
                                <div>
                                    <dt class="text-xs font-semibold text-ink/50">{{ $label }}</dt>
                                    <dd class="mt-0.5 text-ink">{!! nl2br(e($value)) !!}</dd>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-7">
                <div class="rounded-3xl bg-white p-6 shadow-[0_30px_80px_-40px_rgb(8_26_22/0.35)] ring-1 ring-slate-200 sm:p-10">
                    @if (session('status'))
                        <div class="mb-6 flex gap-3 rounded-2xl bg-brand-50 p-4 text-brand-800" role="status"><x-icon name="check" class="mt-0.5 size-5 shrink-0"/>{{ session('status') }}</div>
                    @endif
                    <form method="post" action="{{ route('contact.send') }}" class="grid gap-5 sm:grid-cols-2">
                        @csrf
                        <div class="hidden" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
                        <div>
                            <label for="name" class="label">Your name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="field">
                            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="label">Phone / WhatsApp</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="field">
                        </div>
                        <x-country-select label="Travelling from"/>
                        <div class="sm:col-span-2">
                            <label for="subject" class="label">I’m interested in</label>
                            <select id="subject" name="subject" class="field">
                                <option>A tailor-made journey</option>
                                @foreach (\App\Models\Category::orderBy('sort_order')->pluck('name') as $name)
                                    <option @selected(old('subject') === $name)>{{ $name }}</option>
                                @endforeach
                                <option>Group yatra or family reunion</option>
                                <option>Something else</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="message" class="label">Your message</label>
                            <textarea id="message" name="message" rows="6" required class="field" placeholder="Dates, number of travellers, places you’d love to see…">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col items-start gap-4 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink/50">We never share your details.</p>
                            <button class="btn btn-primary w-full sm:w-auto">Send message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @if ($faqs->isNotEmpty())
        <section class="bg-slate-100 py-16 sm:py-24">
            <div class="container-x max-w-4xl">
                <x-section-heading title="Before you ask" center/>
                <div class="mt-10 space-y-3" x-data="{ open: null }">
                    @foreach ($faqs as $faq)
                        <div class="rounded-2xl bg-white ring-1 ring-slate-200">
                            <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="flex w-full items-center justify-between gap-6 p-5 text-left" :aria-expanded="open === {{ $loop->index }}">
                                <span class="font-semibold text-slate-900">{{ $faq->question }}</span>
                                <x-icon name="chevron-down" class="size-5 shrink-0 text-ink/40 transition" ::class="open === {{ $loop->index }} && 'rotate-180'"/>
                            </button>
                            <div x-show="open === {{ $loop->index }}" x-collapse x-cloak><p class="px-5 pb-5 leading-relaxed text-ink/70">{{ $faq->answer }}</p></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-site-layout>
