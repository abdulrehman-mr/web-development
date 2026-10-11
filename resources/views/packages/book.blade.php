@php
    $display = currencies()->current();
    $secondary = currencies()->secondary();
    $currency = [
        'rate' => currencies()->rate($display) ?: 1,
        'symbol' => $display->symbol,
        'after' => $display->symbol_position === 'after',
        'secondaryRate' => $secondary ? currencies()->rate($secondary) : 0,
        'secondarySymbol' => $secondary?->symbol,
    ];
    $initial = [
        'adults' => (int) old('adults', request('adults', 2)),
        'children' => (int) old('children', 0),
        'roomId' => old('room_type_id') ? (int) old('room_type_id') : null,
        'extras' => array_values((array) old('extras', [])),
        'options' => (object) old('options', []),
        'departureId' => old('departure_id', request('departure')) !== null ? (string) old('departure_id', request('departure')) : null,
    ];
    $hasDepartures = ! empty($pricing['departures']);
    $perLabel = ['adult' => 'per adult', 'person' => 'per person', 'booking' => 'per booking'];
@endphp
<x-site-layout :title="'Book '.$package->title">
    <form method="post" action="{{ route('bookings.store', $package) }}" x-data="bookingConfigurator(@js($pricing), @js($currency), @js($initial))" class="pt-24 pb-28 sm:pt-28 lg:pb-20">
        @csrf
        <div class="container-x">
            <a href="{{ route('packages.show', $package) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900"><x-icon name="arrow-left" class="size-4"/>Back to the journey</a>
            <h1 class="heading-lg mt-3 text-balance text-slate-900">Book {{ $package->title }}</h1>
            <p class="mt-2 text-slate-600">{{ $package->duration_days }} days · private journey · no payment until we confirm availability</p>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-100" role="alert">
                    <p class="font-semibold">Please check the highlighted details.</p>
                    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="min-w-0 space-y-10 lg:col-span-7">
                    {{-- 1. Date --}}
                    <section>
                        <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white">1</span>When would you like to start?</h2>
                        @if ($hasDepartures)
                            <p class="mt-2 text-sm text-slate-600">Join one of our group departures{{ $package->date_mode === 'both' ? ', or pick your own date for a private trip' : '' }}.</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($pricing['departures'] as $d)
                                    @php $full = $d['seatsLeft'] === 0; @endphp
                                    <label @class(['relative flex cursor-pointer flex-col gap-1 rounded-2xl p-4 ring-1 transition has-checked:bg-brand-50 has-checked:ring-2 has-checked:ring-brand-600', $full ? 'cursor-not-allowed bg-slate-50 opacity-60 ring-slate-200' : 'bg-white ring-slate-200 hover:ring-slate-400'])>
                                        <input type="radio" name="departure_id" value="{{ $d['id'] }}" x-model="departureId" @disabled($full) class="sr-only">
                                        <span class="flex items-start justify-between gap-2">
                                            <span class="font-semibold text-slate-900">{{ $d['range'] }}</span>
                                            <x-icon name="check" class="size-5 shrink-0 text-brand-700 opacity-0 [label:has(:checked)_&]:opacity-100"/>
                                        </span>
                                        @if ($d['label'])<span class="text-sm text-slate-600">{{ $d['label'] }}</span>@endif
                                        <span class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                            @if ($full)
                                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-semibold text-slate-600">Sold out</span>
                                            @elseif ($d['seatsLeft'] !== null)
                                                <span @class(['rounded-full px-2 py-0.5 font-semibold', $d['seatsLeft'] <= 4 ? 'bg-amber-100 text-amber-800' : 'bg-brand-50 text-brand-800'])>{{ $d['seatsLeft'] }} {{ Str::plural('seat', $d['seatsLeft']) }} left</span>
                                            @else
                                                <span class="rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-800">Open</span>
                                            @endif
                                            @if ($d['adjustment'] != 0)
                                                <span class="font-medium text-slate-600" x-text="money({{ $d['adjustment'] }}, true) + ' per person'"></span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                                @if ($package->date_mode === 'both')
                                    <label class="flex cursor-pointer flex-col justify-center gap-1 rounded-2xl border-2 border-dashed border-slate-300 bg-white p-4 transition has-checked:border-solid has-checked:border-brand-600 has-checked:bg-brand-50">
                                        <input type="radio" name="departure_id" value="" x-model="departureId" class="sr-only">
                                        <span class="font-semibold text-slate-900">My own dates</span>
                                        <span class="text-sm text-slate-600">A private trip on the day you choose</span>
                                    </label>
                                @endif
                            </div>
                            @error('departure_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @endif
                        @if (! $hasDepartures || $package->date_mode === 'both')
                            <div class="mt-4 max-w-xs" @if ($hasDepartures) x-show="departureId === ''" x-cloak @endif>
                                <label for="start_date" class="label">Start date</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" min="{{ now()->addDay()->toDateString() }}" @if ($hasDepartures) :required="departureId === ''" :disabled="departureId !== ''" @else required @endif class="field">
                                <p class="mt-1 text-xs text-slate-500">Best season: {{ $package->best_season ?: 'year-round' }}. Dates are flexible — we’ll confirm with you.</p>
                            </div>
                        @endif
                    </section>

                    {{-- 2. Travellers --}}
                    <section>
                        <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white">2</span>Who’s travelling?</h2>
                        <div class="mt-4 divide-y divide-slate-200 rounded-2xl ring-1 ring-slate-200">
                            @foreach (['adults' => ['Adults', 'Age 12 and over', 1, 'adultUnit'], 'children' => ['Children', 'Age 2–11', 0, 'childUnit']] as $field => [$label, $hint, $min, $unit])
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $label }}</p>
                                        <p class="text-sm text-slate-500">{{ $hint }} · <span class="font-medium text-slate-700" x-text="money({{ $unit }}) + ' each'"></span></p>
                                    </div>
                                    <div class="flex items-center gap-1 rounded-xl bg-slate-50 p-1 ring-1 ring-slate-200">
                                        <button type="button" @click="step('{{ $field }}', -1, {{ $min }})" :disabled="{{ $field }} <= {{ $min }}" class="grid size-10 place-items-center rounded-lg text-xl text-slate-800 transition hover:bg-white disabled:opacity-30" aria-label="Fewer {{ strtolower($label) }}">−</button>
                                        <input type="number" name="{{ $field }}" x-model.number="{{ $field }}" min="{{ $min }}" max="40" inputmode="numeric" class="w-12 bg-transparent text-center text-lg font-semibold outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" aria-label="{{ $label }}">
                                        <button type="button" @click="step('{{ $field }}', 1, {{ $min }})" :disabled="{{ $field }} >= 40" class="grid size-10 place-items-center rounded-lg text-xl text-slate-800 transition hover:bg-white disabled:opacity-30" aria-label="More {{ strtolower($label) }}">+</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template x-if="tiers.length">
                            <div class="mt-3 rounded-2xl bg-brand-50 p-4 ring-1 ring-brand-100">
                                <p class="flex items-center gap-2 text-sm font-semibold text-brand-900"><x-icon name="users" class="size-4.5"/>Group prices</p>
                                <ul class="mt-2 space-y-1 text-sm">
                                    <template x-for="tier in tiers" :key="tier.min">
                                        <li class="flex justify-between gap-3" :class="adultUnit === tier.price && groupPrice ? 'font-semibold text-brand-800' : 'text-slate-600'">
                                            <span x-text="tier.min + '+ travellers'"></span><span x-text="money(tier.price) + ' per adult'"></span>
                                        </li>
                                    </template>
                                </ul>
                                <p class="mt-2 text-xs text-brand-800" x-show="nextTier" x-text="nextTier ? `Add ${nextTier.min - travellers} more traveller${nextTier.min - travellers > 1 ? 's' : ''} to pay ${money(nextTier.price)} per adult.` : ''"></p>
                                <p class="mt-2 text-xs font-semibold text-brand-800" x-show="groupPrice && !nextTier">You’re getting our best group price.</p>
                            </div>
                        </template>
                    </section>

                    {{-- 3. Room --}}
                    @if (count($pricing['rooms']))
                        <section>
                            <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white">3</span>Choose your room</h2>
                            <p class="mt-1 text-sm text-slate-500">Room prices are per adult for the whole trip. Children share with their parents.</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($pricing['rooms'] as $room)
                                    <label class="flex cursor-pointer flex-col rounded-2xl p-4 ring-1 transition" :class="roomId === {{ $room['id'] }} ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                        <input type="radio" name="room_type_id" value="{{ $room['id'] }}" x-model.number="roomId" class="sr-only">
                                        <span class="flex items-start justify-between gap-3">
                                            <span class="font-semibold text-slate-900">{{ $room['name'] }}</span>
                                            <span class="shrink-0 text-sm font-semibold" :class="{{ $room['adjustment'] }} < 0 ? 'text-brand-700' : 'text-slate-900'"
                                                  x-text="{{ $room['adjustment'] }} === 0 ? 'Included' : money({{ $room['adjustment'] }}, true)"></span>
                                        </span>
                                        <span class="mt-1 text-sm leading-snug text-slate-500">{{ $room['description'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Option groups (e.g. hotel choice, package class) --}}
                    @foreach ($pricing['groups'] as $group)
                        <section>
                            <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white"><x-icon name="check" class="size-4"/></span>{{ $group['name'] }}</h2>
                            @if ($group['description'])<p class="mt-1 text-sm text-slate-500">{{ $group['description'] }}</p>@endif
                            <div class="mt-4 space-y-2">
                                @foreach ($group['options'] as $option)
                                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl p-3 ring-1 transition" :class="chosenOptions['{{ $group['key'] }}'] === '{{ $option['key'] }}' ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                        <input type="radio" name="options[{{ $group['key'] }}]" value="{{ $option['key'] }}" x-model="chosenOptions['{{ $group['key'] }}']" class="sr-only">
                                        @if ($option['hotel'])
                                            <x-media :src="$option['hotel']['image']" :alt="$option['label']" class="size-16 shrink-0 rounded-xl sm:size-20"/>
                                        @endif
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-semibold text-slate-900">{{ $option['label'] }}</span>
                                            @if ($option['hotel'])
                                                <span class="mt-0.5 flex items-center gap-1 text-xs text-slate-500"><x-icon name="map-pin" class="size-3.5 shrink-0"/><span class="truncate">{{ $option['hotel']['address'] }}</span></span>
                                                <span class="mt-0.5 flex text-slate-900" aria-label="{{ $option['hotel']['stars'] }} stars">@for ($i = 0; $i < $option['hotel']['stars']; $i++)<x-icon name="star" class="size-3"/>@endfor</span>
                                            @elseif ($option['description'])
                                                <span class="mt-0.5 block text-sm leading-snug text-slate-500">{{ $option['description'] }}</span>
                                            @endif
                                        </span>
                                        <span class="shrink-0 text-right text-sm">
                                            <span class="block font-semibold" :class="{{ $option['price'] }} < 0 ? 'text-brand-700' : 'text-slate-900'" x-text="{{ $option['price'] }} === 0 ? 'Included' : money({{ $option['price'] }}, true)"></span>
                                            @if ($option['price'] != 0)<span class="block text-xs text-slate-500">{{ $perLabel[$option['per']] }}</span>@endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    {{-- 4. Extras --}}
                    @if (count($pricing['extras']))
                        <section>
                            <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white">{{ count($pricing['rooms']) ? 4 : 3 }}</span>Add extras <span class="text-sm font-normal text-slate-500">(optional)</span></h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($pricing['extras'] as $extra)
                                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl p-4 ring-1 transition" :class="chosenExtras.includes('{{ $extra['key'] }}') ? 'bg-brand-50 ring-2 ring-brand-700' : 'bg-white ring-slate-200 hover:ring-slate-300'">
                                        <input type="checkbox" name="extras[]" value="{{ $extra['key'] }}" x-model="chosenExtras" class="mt-1 size-4.5 shrink-0 accent-brand-700">
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-semibold text-slate-900">{{ $extra['name'] }}</span>
                                            @if ($extra['description'])<span class="mt-0.5 block text-sm text-slate-500">{{ $extra['description'] }}</span>@endif
                                        </span>
                                        <span class="shrink-0 text-right text-sm">
                                            <span class="block font-semibold text-slate-900" x-text="money({{ $extra['price'] }}, true)"></span>
                                            <span class="block text-xs text-slate-500">{{ $extra['per'] === 'person' ? 'per person' : 'per booking' }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- 5. Details --}}
                    <section>
                        <h2 class="flex items-center gap-3 text-lg font-bold text-slate-900"><span class="grid size-7 place-items-center rounded-full bg-brand-700 text-sm text-white">{{ 3 + (count($pricing['rooms']) ? 1 : 0) + (count($pricing['extras']) ? 1 : 0) }}</span>Your details</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="full_name" class="label">Full name</label>
                                <input id="full_name" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" class="field">
                            </div>
                            <div>
                                <label for="email" class="label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                            </div>
                            <div>
                                <label for="phone" class="label">Phone / WhatsApp</label>
                                <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="field">
                            </div>
                            <x-country-select label="Travelling from" :value="setting('booking_default_country')" class="sm:col-span-2"/>
                            <div class="sm:col-span-2">
                                <label for="special_requests" class="label">Anything we should know? <span class="font-normal text-slate-400">(optional)</span></label>
                                <textarea id="special_requests" name="special_requests" rows="3" class="field" placeholder="Dietary needs, a village to visit, celebrations…">{{ old('special_requests') }}</textarea>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Summary --}}
                <aside class="min-w-0 lg:col-span-5">
                    <div class="rounded-2xl bg-white p-5 shadow-[0_24px_60px_-30px_rgb(15_23_42/0.35)] ring-1 ring-slate-200 sm:p-6 lg:sticky lg:top-24">
                        <div class="flex gap-4">
                            <x-media :src="$package->hero_image_url" class="size-20 shrink-0 rounded-xl"/>
                            <div class="min-w-0">
                                <p class="font-semibold leading-snug text-slate-900">{{ $package->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $package->duration_days }} days · <span x-text="travellers + (travellers === 1 ? ' traveller' : ' travellers')"></span></p>
                                <p class="text-sm text-slate-500" x-show="room" x-text="room ? room.name : ''"></p>
                            </div>
                        </div>
                        <dl class="mt-5 space-y-2.5 border-t border-slate-100 pt-4 text-sm">
                            <template x-for="(line, i) in lines" :key="i">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-600" x-text="line.label"></dt>
                                    <dd class="shrink-0 font-medium" :class="line.amount < 0 ? 'text-brand-700' : 'text-slate-900'" x-text="money(line.amount)"></dd>
                                </div>
                            </template>
                        </dl>
                        <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-slate-200 pt-4">
                            <span class="font-semibold text-slate-900">Estimated total</span>
                            <span class="text-right">
                                <span class="block text-3xl font-bold tracking-tight text-slate-900" x-text="money(total)">{{ money($package->price * 2) }}</span>
                                <span class="block text-xs text-slate-500" x-show="currency.secondaryRate" x-text="approx(total)"></span>
                            </span>
                        </div>
                        <button class="btn btn-primary mt-5 hidden w-full py-4 text-base lg:flex">Request this journey</button>
                        <ul class="mt-4 space-y-2 text-xs text-slate-500">
                            <li class="flex gap-2"><x-icon name="check" class="size-4 shrink-0 text-brand-600"/>No payment now — we confirm availability within 24 hours.</li>
                            <li class="flex gap-2"><x-icon name="check" class="size-4 shrink-0 text-brand-600"/>Then a {{ (int) setting('deposit_percent', 25) }}% deposit secures your dates.</li>
                            <li class="flex gap-2"><x-icon name="check" class="size-4 shrink-0 text-brand-600"/>Prices shown in {{ $display->code }}; final quote in writing.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>

        {{-- Mobile total + submit --}}
        <div id="mobile-book-bar" class="fixed inset-x-0 bottom-0 z-30 flex items-center justify-between gap-4 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
            <div>
                <p class="text-[0.7rem] text-slate-500">Estimated total</p>
                <p class="text-xl font-bold tracking-tight text-slate-900" x-text="money(total)"></p>
            </div>
            <button class="btn btn-primary">Request journey</button>
        </div>
    </form>
</x-site-layout>
