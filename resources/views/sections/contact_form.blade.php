@php $tag = $s['heading_level']; @endphp
<x-builder.section :section="$section">
    <div @class(['container-x grid gap-12 lg:grid-cols-12 lg:gap-16', 'max-w-4xl' => ! $s['show_details']])>
        <div @class(['lg:col-span-5' => $s['show_details'], 'lg:col-span-12' => ! $s['show_details']])>
            <{{ $tag }} @class(['text-slate-900', $tag === 'h1' ? 'heading-xl' : 'heading-lg'])>{{ $s['title'] }}</{{ $tag }}>
            @if ($s['intro'])<p class="mt-5 text-lg leading-relaxed text-ink/70">{{ $s['intro'] }}</p>@endif

            @if ($s['show_details'])
                <dl class="mt-10 space-y-6">
                    @foreach ([
                        ['phone', setting('label_phone_secondary', 'Canada'), setting('contact_phone_ca')],
                        ['phone', setting('label_phone_primary', 'Pakistan'), setting('contact_phone')],
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
            @endif
        </div>

        <div @class(['lg:col-span-7' => $s['show_details'], 'lg:col-span-12' => ! $s['show_details']])>
            <div class="rounded-3xl bg-white p-6 shadow-[0_30px_80px_-40px_rgb(8_26_22/0.35)] ring-1 ring-slate-200 sm:p-10">
                @if (session('status'))
                    <div class="mb-6 flex gap-3 rounded-2xl bg-brand-50 p-4 text-brand-800" role="status"><x-icon name="check" class="mt-0.5 size-5 shrink-0"/>{{ session('status') }}</div>
                @endif
                <form method="post" action="{{ route('contact.send') }}" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <div class="hidden" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
                    <div>
                        <label for="name-{{ $section->id }}" class="label">Your name</label>
                        <input id="name-{{ $section->id }}" name="name" value="{{ old('name') }}" required autocomplete="name" class="field">
                        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email-{{ $section->id }}" class="label">Email</label>
                        <input id="email-{{ $section->id }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone-{{ $section->id }}" class="label">Phone / WhatsApp</label>
                        <input id="phone-{{ $section->id }}" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="field">
                    </div>
                    <x-country-select label="Travelling from"/>
                    <div class="sm:col-span-2">
                        <label for="subject-{{ $section->id }}" class="label">I’m interested in</label>
                        <select id="subject-{{ $section->id }}" name="subject" class="field">
                            <option>A tailor-made journey</option>
                            @foreach ($subjects as $name)
                                <option @selected(old('subject') === $name)>{{ $name }}</option>
                            @endforeach
                            <option>Something else</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message-{{ $section->id }}" class="label">Your message</label>
                        <textarea id="message-{{ $section->id }}" name="message" rows="6" required class="field" placeholder="Dates, number of travellers, places you’d love to see…">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col items-start gap-4 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-ink/50">{{ $s['privacy_note'] }}</p>
                        <button class="btn btn-primary w-full sm:w-auto">{{ $s['button_label'] ?: 'Send message' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-builder.section>
