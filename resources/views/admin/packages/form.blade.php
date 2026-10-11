@php
    $editing = $package->exists;
    $days = collect(old('days', $package->days->map(fn ($d) => $d->only('title', 'description', 'meals', 'hotel_id', 'activities'))->all()))
        ->map(fn ($d) => array_merge($d, [
            'hotel_id' => (string) ($d['hotel_id'] ?? ''),
            'activities' => collect($d['activities'] ?? [])->map(fn ($a) => [
                'time' => $a['time'] ?? '', 'title' => $a['title'] ?? '', 'description' => $a['description'] ?? '',
                'image' => $a['image'] ?? '', 'preview' => media_url($a['image'] ?? null),
            ])->values()->all(),
        ]))->values()->all();
    $selectedDestinations = collect(old('destination_ids', $package->destinations->pluck('id')->all()))->map(fn ($id) => (int) $id)->all();
    $lines = fn ($field) => old($field, implode("\n", $package->{$field} ?? []));
    $tiers = old('tiers', $package->group_tiers ?? []);
    $departureRows = old('departures', $package->exists
        ? $package->departures()->withCount(['bookings' => fn ($q) => $q->where('status', '!=', 'cancelled')])->get()->map(fn ($d) => [
            'id' => $d->id, 'starts_on' => $d->starts_on->toDateString(), 'seats' => $d->seats, 'price_adjustment' => $d->price_adjustment ?: '',
            'label' => $d->label, 'is_closed' => $d->is_closed, 'booked' => $d->seatsTaken(),
        ])->all()
        : []);
    $extras = old('extras', $package->extras ?? []);
    $roomOptions = $package->room_options ?? [];
    $defaultRoom = (int) old('default_room_type_id', $package->default_room_type_id ?? $roomTypes->first()?->id);
    $base = currencies()->base()->code;
    $selectedVibes = collect(old('vibe_ids', $package->vibes?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $groups = collect(old('groups', $package->option_groups ?? []))->map(fn ($g) => [...$g, 'options' => collect($g['options'] ?? [])->map(fn ($o) => [...$o, 'hotel_id' => (string) ($o['hotel_id'] ?? '')])->all()])->all();
@endphp
<x-admin-layout :title="$editing ? 'Edit journey' : 'New journey'">
    <x-slot:actions>
        @if ($editing)
            <a href="{{ route('packages.show', $package) }}" target="_blank" class="btn btn-ghost py-2.5"><x-icon name="eye" class="size-4"/><span class="hidden sm:inline">Preview</span></a>
        @endif
        <button form="package-form" class="btn btn-primary py-2.5">Save</button>
    </x-slot:actions>

    <form id="package-form" method="post" enctype="multipart/form-data" action="{{ $editing ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="min-w-0 space-y-6 xl:col-span-2">
            <x-admin.card title="Basics">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="title" label="Title" :value="$package->title" required class="sm:col-span-2"/>
                    <x-admin.textarea name="summary" label="Short summary" :value="$package->summary" rows="2" help="Shown on journey cards (max 500 characters)." class="sm:col-span-2"/>
                    <x-admin.textarea name="overview" label="Overview" :value="$package->overview" rows="6" help="Leave a blank line between paragraphs." class="sm:col-span-2"/>
                </div>
            </x-admin.card>

            {{-- Prices & options --}}
            <x-admin.card title="Prices & options" :description="'All prices in '.$base.'. Guests see them converted to their currency.'">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="child_price" type="number" step="1" min="0" label="Child price (age 2–11)" :value="$package->child_price" :help="'Leave blank to charge '.(int) (\App\Support\BookingPricing::CHILD_RATE * 100).'% of the adult price.'"/>
                </div>

                {{-- Group tiers --}}
                <div class="mt-6" x-data="{ tiers: @js(array_values($tiers)) }">
                    <p class="text-sm font-semibold text-slate-900">Group prices</p>
                    <p class="text-xs text-slate-500">Lower the adult price when a booking has at least this many travellers (adults + children).</p>
                    <div class="mt-3 space-y-2">
                        <template x-for="(tier, i) in tiers" :key="i">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-slate-600">From</span>
                                <input type="number" min="2" :name="`tiers[${i}][min]`" x-model="tier.min" class="field w-20 px-2 py-2" aria-label="Minimum travellers" required>
                                <span class="text-sm text-slate-600">travellers:</span>
                                <input type="number" min="0" step="1" :name="`tiers[${i}][price]`" x-model="tier.price" class="field w-32 px-2 py-2" aria-label="Price per adult" required>
                                <span class="text-sm text-slate-600">per adult</span>
                                <button type="button" @click="tiers.splice(i, 1)" class="ml-auto rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Remove group price"><x-icon name="trash" class="size-4"/></button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="tiers.push({ min: tiers.length ? Number(tiers[tiers.length - 1].min) + 4 : 6, price: '' })" class="btn btn-ghost mt-3 py-2"><x-icon name="plus" class="size-4"/>Add group price</button>
                </div>

                {{-- Rooms --}}
                <div class="mt-6">
                    <p class="text-sm font-semibold text-slate-900">Room options</p>
                    <p class="text-xs text-slate-500">Tick the rooms offered on this journey and set the price change per adult (negative for a discount). Manage the list in <a href="{{ route('admin.room-types.edit') }}" class="underline">Room types</a>.</p>
                    <div class="mt-3 divide-y divide-slate-100 rounded-xl ring-1 ring-slate-200">
                        @foreach ($roomTypes as $type)
                            @php $option = $roomOptions[$type->id] ?? null; @endphp
                            <div class="flex flex-wrap items-center gap-3 p-3">
                                <label class="flex min-w-0 flex-1 items-center gap-3">
                                    <input type="checkbox" name="rooms[{{ $type->id }}][enabled]" value="1" @checked($option === null || ($option['enabled'] ?? false)) class="size-4 accent-brand-700">
                                    <span class="min-w-0"><span class="block text-sm font-medium text-slate-900">{{ $type->name }}</span><span class="block truncate text-xs text-slate-500">{{ $type->description }}</span></span>
                                </label>
                                <input type="number" step="1" name="rooms[{{ $type->id }}][adjustment]" value="{{ $option['adjustment'] ?? '' }}" placeholder="{{ $type->default_adjustment + 0 }}" class="field w-28 px-2 py-2" aria-label="{{ $type->name }} price change per adult">
                                <label class="flex items-center gap-1.5 text-xs text-slate-600"><input type="radio" name="default_room_type_id" value="{{ $type->id }}" @checked($defaultRoom === $type->id) class="accent-brand-700"> Default</label>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Leave the price blank to use the room type’s standard price shown in grey.</p>
                </div>

                {{-- Option groups --}}
                <div class="mt-6" x-data="{ groups: @js(array_values($groups)) }">
                    <p class="text-sm font-semibold text-slate-900">Choice groups</p>
                    <p class="text-xs text-slate-500">Let guests pick one option per group, e.g. “Hotel in Makkah” (pick from your stays), or “Package class” with Economy / Standard / VIP. The first option is pre-selected; prices can be negative.</p>
                    <div class="mt-3 space-y-4">
                        <template x-for="(group, g) in groups" :key="g">
                            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                                    <input :name="`groups[${g}][name]`" x-model="group.name" placeholder="Group name, e.g. Hotel in Makkah" class="field py-2" required>
                                    <button type="button" @click="groups.splice(g, 1)" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Remove group"><x-icon name="trash" class="size-4"/></button>
                                    <input :name="`groups[${g}][description]`" x-model="group.description" placeholder="Help text (optional), e.g. 5 nights, price per adult" class="field py-2 sm:col-span-2">
                                </div>
                                <div class="mt-3 space-y-2">
                                    <template x-for="(option, o) in group.options" :key="o">
                                        <div class="grid gap-2 rounded-lg bg-white p-2 ring-1 ring-slate-200 sm:grid-cols-[1fr_1fr_7rem_8rem_auto]">
                                            <select :name="`groups[${g}][options][${o}][hotel_id]`" x-model="option.hotel_id" class="field py-2" aria-label="Hotel">
                                                <option value="">— Not a hotel —</option>
                                                @foreach ($hotels as $h)
                                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                                @endforeach
                                            </select>
                                            <input :name="`groups[${g}][options][${o}][label]`" x-model="option.label" placeholder="Label (blank = hotel name)" class="field py-2">
                                            <input type="number" step="1" :name="`groups[${g}][options][${o}][price]`" x-model="option.price" placeholder="± price" class="field py-2" required>
                                            <select :name="`groups[${g}][options][${o}][per]`" x-model="option.per" class="field py-2"><option value="adult">per adult</option><option value="person">per person</option><option value="booking">per booking</option></select>
                                            <button type="button" @click="group.options.splice(o, 1)" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Remove option"><x-icon name="x" class="size-4"/></button>
                                            <input :name="`groups[${g}][options][${o}][description]`" x-model="option.description" placeholder="Description (optional)" class="field py-2 sm:col-span-5">
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="group.options.push({ hotel_id: '', label: '', price: 0, per: 'adult', description: '' })" class="btn btn-ghost mt-2 py-1.5 text-xs"><x-icon name="plus" class="size-3.5"/>Add option</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="groups.push({ name: '', description: '', options: [{ hotel_id: '', label: '', price: 0, per: 'adult', description: '' }] })" class="btn btn-ghost mt-3 py-2"><x-icon name="plus" class="size-4"/>Add choice group</button>
                </div>

                {{-- Extras --}}
                <div class="mt-6" x-data="{ extras: @js(array_values($extras)) }">
                    <p class="text-sm font-semibold text-slate-900">Optional extras</p>
                    <p class="text-xs text-slate-500">Add-ons guests can tick when booking, like an upgrade, a domestic flight or a photographer.</p>
                    <div class="mt-3 space-y-3">
                        <template x-for="(extra, i) in extras" :key="i">
                            <div class="grid gap-2 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 sm:grid-cols-[1fr_8rem_9rem_auto]">
                                <input :name="`extras[${i}][name]`" x-model="extra.name" placeholder="Name, e.g. Upgrade to 5-star hotels" class="field py-2" required>
                                <input type="number" step="1" :name="`extras[${i}][price]`" x-model="extra.price" placeholder="Price" class="field py-2" required>
                                <select :name="`extras[${i}][per]`" x-model="extra.per" class="field py-2"><option value="person">per person</option><option value="booking">per booking</option></select>
                                <button type="button" @click="extras.splice(i, 1)" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Remove extra"><x-icon name="trash" class="size-4"/></button>
                                <input :name="`extras[${i}][description]`" x-model="extra.description" placeholder="Short description (optional)" class="field py-2 sm:col-span-4">
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="extras.push({ name: '', price: '', per: 'person', description: '' })" class="btn btn-ghost mt-3 py-2"><x-icon name="plus" class="size-4"/>Add extra</button>
                </div>
            </x-admin.card>

            {{-- Dates & departures --}}
            <x-admin.card title="Dates & departures" description="Let guests pick any date, or join fixed group departures (batches) with limited seats."
                          x-data="{
                            mode: {{ Js::from(old('date_mode', $package->date_mode ?? 'any')) }},
                            rows: {{ Js::from(array_values($departureRows)) }},
                            gen: { from: '', every: 'week', until: '', count: 6, seats: 12, price_adjustment: '', label: '' },
                            addRow() { this.rows.push({ id: null, starts_on: '', seats: 12, price_adjustment: '', label: '', is_closed: false, booked: 0 }) },
                            generate() {
                                if (!this.gen.from) return;
                                const existing = new Set(this.rows.map((r) => r.starts_on));
                                const until = this.gen.until ? new Date(this.gen.until + 'T00:00:00') : null;
                                let d = new Date(this.gen.from + 'T00:00:00');
                                for (let n = 0; n < Math.min(Number(this.gen.count) || 1, 60); n++) {
                                    if (until && d > until) break;
                                    const iso = [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
                                    if (!existing.has(iso)) this.rows.push({ id: null, starts_on: iso, seats: this.gen.seats, price_adjustment: this.gen.price_adjustment, label: this.gen.label, is_closed: false, booked: 0 });
                                    if (this.gen.every === 'week') d.setDate(d.getDate() + 7);
                                    else if (this.gen.every === '2weeks') d.setDate(d.getDate() + 14);
                                    else if (this.gen.every === 'day') d.setDate(d.getDate() + 1);
                                    else d.setMonth(d.getMonth() + 1);
                                }
                                this.rows.sort((a, b) => (a.starts_on || '9').localeCompare(b.starts_on || '9'));
                            },
                          }">
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach (['any' => ['Any date', 'Guests choose their own start date.'], 'departures' => ['Fixed departures', 'Guests join one of your batches.'], 'both' => ['Both', 'Batches, or a private trip on their own date.']] as $value => [$label, $hint])
                        <label class="flex cursor-pointer gap-3 rounded-xl p-3 ring-1 ring-slate-200 has-checked:bg-brand-50 has-checked:ring-2 has-checked:ring-brand-600">
                            <input type="radio" name="date_mode" value="{{ $value }}" x-model="mode" class="mt-1 accent-brand-700">
                            <span><span class="block text-sm font-semibold">{{ $label }}</span><span class="text-xs text-ink/55">{{ $hint }}</span></span>
                        </label>
                    @endforeach
                </div>

                <div x-show="mode !== 'any'" x-cloak class="mt-5 space-y-4">
                    {{-- Recurring generator --}}
                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <p class="text-sm font-semibold text-slate-900">Add recurring departures</p>
                        <p class="text-xs text-ink/55">e.g. every Saturday from 1 March until 30 June, 12 seats each. You can edit or remove any date afterwards.</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            <label class="text-xs font-medium text-slate-600">First departure<input type="date" x-model="gen.from" class="field mt-1 py-2"></label>
                            <label class="text-xs font-medium text-slate-600">Repeat
                                <select x-model="gen.every" class="field mt-1 py-2">
                                    <option value="week">Every week</option><option value="2weeks">Every 2 weeks</option><option value="month">Every month</option><option value="day">Every day</option>
                                </select>
                            </label>
                            <label class="text-xs font-medium text-slate-600">Until (optional)<input type="date" x-model="gen.until" class="field mt-1 py-2"></label>
                            <label class="text-xs font-medium text-slate-600">How many at most<input type="number" min="1" max="60" x-model="gen.count" class="field mt-1 py-2"></label>
                            <label class="text-xs font-medium text-slate-600">Seats each<input type="number" min="1" x-model="gen.seats" class="field mt-1 py-2" placeholder="Unlimited"></label>
                            <label class="text-xs font-medium text-slate-600">Price change per person<input type="number" step="0.01" x-model="gen.price_adjustment" class="field mt-1 py-2" placeholder="0"></label>
                            <label class="text-xs font-medium text-slate-600 sm:col-span-2">Name (optional)<input x-model="gen.label" class="field mt-1 py-2" placeholder="e.g. Gurpurab batch"></label>
                            <div class="flex items-end"><button type="button" @click="generate()" :disabled="!gen.from" class="btn btn-primary w-full py-2.5 disabled:opacity-50"><x-icon name="calendar" class="size-4"/>Add dates</button></div>
                        </div>
                    </div>

                    {{-- Departure list --}}
                    <div class="space-y-2">
                        <div class="hidden grid-cols-[9rem_5.5rem_7rem_1fr_auto] gap-2 px-1 text-xs font-medium text-slate-500 md:grid">
                            <span>Start date</span><span>Seats</span><span>± per person</span><span>Name</span><span></span>
                        </div>
                        <template x-for="(row, k) in rows" :key="k">
                            <div class="grid gap-2 rounded-xl p-2 ring-1 ring-slate-200 md:grid-cols-[9rem_5.5rem_7rem_1fr_auto] md:items-center" :class="row.is_closed ? 'bg-slate-50 opacity-70' : 'bg-white'">
                                <input type="hidden" :name="`departures[${k}][id]`" :value="row.id ?? ''">
                                <input type="date" class="field py-2" :name="`departures[${k}][starts_on]`" x-model="row.starts_on" required>
                                <input type="number" min="1" class="field py-2" :name="`departures[${k}][seats]`" x-model="row.seats" placeholder="∞" aria-label="Seats">
                                <input type="number" step="0.01" class="field py-2" :name="`departures[${k}][price_adjustment]`" x-model="row.price_adjustment" placeholder="0" aria-label="Price change per person">
                                <input class="field py-2" :name="`departures[${k}][label]`" x-model="row.label" placeholder="Name (optional)" aria-label="Name">
                                <div class="flex items-center justify-between gap-2 md:justify-end">
                                    <span class="text-xs whitespace-nowrap text-slate-500" x-text="(row.booked || 0) + (row.seats ? ' / ' + row.seats : '') + ' booked'"></span>
                                    <label class="flex items-center gap-1 text-xs text-slate-600"><input type="hidden" :name="`departures[${k}][is_closed]`" :value="row.is_closed ? 1 : 0"><input type="checkbox" x-model="row.is_closed" class="accent-brand-700">Closed</label>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-500/10" @click="rows.splice(k, 1)" :title="row.booked ? 'Has bookings: it will be closed instead of deleted' : 'Remove'" aria-label="Remove departure"><x-icon name="trash" class="size-4"/></button>
                                </div>
                            </div>
                        </template>
                        <p x-show="!rows.length" class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-ink/50">No departures yet. Add recurring dates above, or one at a time.</p>
                        <button type="button" @click="addRow()" class="btn btn-ghost w-full py-2.5"><x-icon name="plus" class="size-4"/>Add a single departure</button>
                    </div>
                </div>
            </x-admin.card>

            {{-- Itinerary builder --}}
            <x-admin.card title="Day-by-day itinerary" description="Add each day, and choose where guests sleep that night." x-data="{ days: {{ Js::from($days) }} }">
                <ol class="space-y-3">
                    <template x-for="(day, i) in days" :key="i">
                        <li class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <span class="rounded-full bg-brand-800 px-3 py-1 text-xs font-semibold text-white" x-text="'Day ' + (i + 1)"></span>
                                <div class="flex gap-1 text-ink/50">
                                    <button type="button" class="rounded-lg p-1.5 hover:bg-slate-200 disabled:opacity-30" :disabled="i === 0" @click="[days[i - 1], days[i]] = [days[i], days[i - 1]]" aria-label="Move up">↑</button>
                                    <button type="button" class="rounded-lg p-1.5 hover:bg-slate-200 disabled:opacity-30" :disabled="i === days.length - 1" @click="[days[i + 1], days[i]] = [days[i], days[i + 1]]" aria-label="Move down">↓</button>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-500/10" @click="days.splice(i, 1)" aria-label="Remove day"><x-icon name="trash" class="size-4"/></button>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input class="field sm:col-span-2" :name="`days[${i}][title]`" x-model="day.title" placeholder="Title, e.g. Arrival in Lahore" required>
                                <textarea class="field sm:col-span-2" rows="3" :name="`days[${i}][description]`" x-model="day.description" placeholder="What happens on this day"></textarea>
                                <select class="field" :name="`days[${i}][hotel_id]`" x-model="day.hotel_id">
                                    <option value="">No overnight stay / in transit</option>
                                    @foreach ($hotels as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}{{ $h->destination ? ' — '.$h->destination->name : '' }}</option>
                                    @endforeach
                                </select>
                                <input class="field" :name="`days[${i}][meals]`" x-model="day.meals" placeholder="Meals, e.g. Breakfast, Dinner">
                            </div>

                            {{-- Timed activities with a photo, e.g. "10:00 am · Badshahi Mosque" --}}
                            <div class="mt-4 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                <p class="mb-2 text-xs font-semibold text-slate-600">Activities & sights <span class="font-normal text-slate-500">· shown as a timeline with photos</span></p>
                                <div class="space-y-2">
                                    <template x-for="(act, j) in day.activities" :key="j">
                                        <div class="grid gap-2 rounded-xl bg-white p-3 ring-1 ring-slate-200 sm:grid-cols-[1fr_7rem]">
                                            <div class="grid min-w-0 gap-2 sm:grid-cols-[8rem_1fr]">
                                                <input class="field py-2" :name="`days[${i}][activities][${j}][time]`" x-model="act.time" placeholder="Morning · 10 am">
                                                <input class="field py-2" :name="`days[${i}][activities][${j}][title]`" x-model="act.title" placeholder="Sight or activity">
                                                <textarea class="field py-2 sm:col-span-2" rows="2" :name="`days[${i}][activities][${j}][description]`" x-model="act.description" placeholder="Short description"></textarea>
                                                <input class="field py-2 sm:col-span-2" :name="`days[${i}][activities][${j}][image]`" x-model="act.image" @input="act.preview = act.image" placeholder="Photo URL (or upload)">
                                            </div>
                                            <div class="flex gap-2 sm:flex-col">
                                                <label class="media-fallback relative block aspect-[4/3] w-28 cursor-pointer overflow-hidden rounded-lg sm:w-full" title="Upload a photo">
                                                    <img x-show="act.preview" :src="act.preview" alt="" class="absolute inset-0 size-full object-cover">
                                                    <span x-show="!act.preview" class="absolute inset-0 grid place-items-center text-xs font-semibold text-white/90">+ Photo</span>
                                                    <input type="file" accept="image/*" class="sr-only" :name="`days[${i}][activities][${j}][image_file]`" @change="if ($event.target.files[0]) act.preview = URL.createObjectURL($event.target.files[0])">
                                                </label>
                                                <div class="flex justify-end gap-1 text-ink/50">
                                                    <button type="button" class="rounded-lg p-1.5 hover:bg-slate-100 disabled:opacity-30" :disabled="j === 0" @click="[day.activities[j - 1], day.activities[j]] = [day.activities[j], day.activities[j - 1]]" aria-label="Move up">↑</button>
                                                    <button type="button" class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-500/10" @click="day.activities.splice(j, 1)" aria-label="Remove activity"><x-icon name="trash" class="size-4"/></button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="(day.activities ??= []).push({ time: '', title: '', description: '', image: '', preview: '' })" class="mt-2 inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-50"><x-icon name="plus" class="size-4"/>Add activity</button>
                            </div>
                        </li>
                    </template>
                </ol>
                <p x-show="!days.length" class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-ink/50">No days yet.</p>
                <button type="button" @click="days.push({ title: '', description: '', meals: '', hotel_id: '', activities: [] })" class="btn btn-ghost mt-4 w-full py-2.5"><x-icon name="plus" class="size-4"/>Add day</button>
            </x-admin.card>

            <x-admin.card title="Details lists" description="One item per line.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.textarea name="highlights" label="Highlights" :value="$lines('highlights')" rows="6" class="sm:col-span-2"/>
                    <x-admin.textarea name="inclusions" label="What’s included" :value="$lines('inclusions')" rows="7"/>
                    <x-admin.textarea name="exclusions" label="Not included" :value="$lines('exclusions')" rows="7"/>
                </div>
            </x-admin.card>

            <x-admin.card title="Photos">
                <x-admin.image name="hero_image" label="Main photo" :value="$package->hero_image" help="Wide landscape, at least 2000px."/>
                <div class="mt-6">
                    <x-admin.textarea name="gallery" label="Gallery image URLs / paths (one per line)" :value="$lines('gallery')" rows="4"/>
                    <label class="mt-2 flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-ink/60 hover:border-brand-600" x-data="{ n: 0 }">
                        <x-icon name="plus" class="size-4"/><span x-text="n ? n + ' photo(s) selected' : 'Upload gallery photos'"></span>
                        <input type="file" name="gallery_files[]" accept="image/*" multiple class="sr-only" @change="n = $event.target.files.length">
                    </label>
                </div>
            </x-admin.card>
        </div>

        <div class="min-w-0 space-y-6">
            <x-admin.card title="Publishing">
                <div class="space-y-4">
                    <x-admin.toggle name="is_published" label="Published" :checked="$package->is_published" help="Visible on the website."/>
                    <x-admin.toggle name="is_featured" label="Featured" :checked="$package->is_featured" help="Shown on the home page."/>
                    <x-admin.input name="sort_order" type="number" label="Sort order" :value="$package->sort_order ?? 0" help="Lower numbers appear first."/>
                    <x-admin.input name="slug" label="URL slug" :value="$package->slug" help="Leave blank to generate from the title."/>
                </div>
            </x-admin.card>

            <x-admin.card title="Price & trip facts">
                <div class="grid grid-cols-2 gap-4">
                    <x-admin.input name="price" type="number" step="1" min="0" :label="'Price ('.currencies()->base()->code.' per person)'" :value="$package->price" required/>
                    <x-admin.input name="original_price" type="number" step="1" min="0" label="Was (optional)" :value="$package->original_price"/>
                    <x-admin.input name="duration_days" type="number" min="1" label="Days" :value="$package->duration_days" required/>
                    <x-admin.input name="rating" type="number" step="0.1" min="0" max="5" label="Rating" :value="$package->rating"/>
                    <div class="col-span-2">
                        <label for="f_category_id" class="label">Experience</label>
                        <select id="f_category_id" name="category_id" class="field">
                            <option value="">—</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id', $package->category_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="f_difficulty" class="label">Pace</label>
                        <select id="f_difficulty" name="difficulty" class="field">
                            @foreach (\App\Models\Package::DIFFICULTIES as $d)
                                <option @selected(old('difficulty', $package->difficulty) === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-admin.input name="group_size" label="Group size" :value="$package->group_size" placeholder="Private · 2–12 guests" class="col-span-2"/>
                    <x-admin.input name="best_season" label="Best season" :value="$package->best_season" placeholder="Oct – Mar"/>
                    <x-admin.input name="start_city" label="Starts in" :value="$package->start_city" placeholder="Lahore"/>
                </div>
            </x-admin.card>

            <x-admin.card title="Country & vibes">
                <label for="f_country" class="label">Country</label>
                <select id="f_country" name="country" class="field">
                    @foreach (config('countries') as $code => $countryName)
                        <option value="{{ $code }}" @selected(old('country', $package->country ?? 'PK') === $code)>{{ $countryName }}</option>
                    @endforeach
                </select>
                <p class="mt-4 mb-2 text-sm font-medium text-slate-700">Vibes</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($vibes as $vibe)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50">
                            <input type="checkbox" name="vibe_ids[]" value="{{ $vibe->id }}" @checked(in_array($vibe->id, $selectedVibes, true)) class="size-4 accent-brand-700">
                            <x-icon :name="$vibe->icon" class="size-4 text-slate-500"/>{{ $vibe->name }}
                        </label>
                    @endforeach
                </div>
            </x-admin.card>

            <x-admin.card title="Destinations visited">
                <div class="max-h-72 space-y-1 overflow-y-auto">
                    @foreach ($destinations as $d)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-white">
                            <input type="checkbox" name="destination_ids[]" value="{{ $d->id }}" @checked(in_array($d->id, $selectedDestinations, true)) class="size-4 rounded accent-brand-700">
                            {{ $d->name }} <span class="ml-auto text-xs text-ink/40">{{ $d->region }}</span>
                        </label>
                    @endforeach
                </div>
            </x-admin.card>

            <button class="btn btn-primary w-full">Save journey</button>
        </div>
    </form>
</x-admin-layout>
