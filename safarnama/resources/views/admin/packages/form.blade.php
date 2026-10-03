@php
    $editing = $package->exists;
    $days = collect(old('days', $package->days->map(fn ($d) => $d->only('title', 'description', 'meals', 'hotel_id'))->all()))
        ->map(fn ($d) => array_merge($d, ['hotel_id' => (string) ($d['hotel_id'] ?? '')]))->values()->all();
    $selectedDestinations = collect(old('destination_ids', $package->destinations->pluck('id')->all()))->map(fn ($id) => (int) $id)->all();
    $lines = fn ($field) => old($field, implode("\n", $package->{$field} ?? []));
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

            {{-- Itinerary builder --}}
            <x-admin.card title="Day-by-day itinerary" description="Add each day, and choose where guests sleep that night." x-data="{ days: {{ Js::from($days) }} }">
                <ol class="space-y-3">
                    <template x-for="(day, i) in days" :key="i">
                        <li class="rounded-2xl bg-marble-50 p-4 ring-1 ring-marble-200">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <span class="rounded-full bg-pine-800 px-3 py-1 text-xs font-semibold text-marble-50" x-text="'Day ' + (i + 1)"></span>
                                <div class="flex gap-1 text-ink/50">
                                    <button type="button" class="rounded-lg p-1.5 hover:bg-marble-200 disabled:opacity-30" :disabled="i === 0" @click="[days[i - 1], days[i]] = [days[i], days[i - 1]]" aria-label="Move up">↑</button>
                                    <button type="button" class="rounded-lg p-1.5 hover:bg-marble-200 disabled:opacity-30" :disabled="i === days.length - 1" @click="[days[i + 1], days[i]] = [days[i], days[i + 1]]" aria-label="Move down">↓</button>
                                    <button type="button" class="rounded-lg p-1.5 text-sandstone-600 hover:bg-sandstone-500/10" @click="days.splice(i, 1)" aria-label="Remove day"><x-icon name="trash" class="size-4"/></button>
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
                        </li>
                    </template>
                </ol>
                <p x-show="!days.length" class="rounded-2xl border border-dashed border-marble-300 p-6 text-center text-sm text-ink/50">No days yet.</p>
                <button type="button" @click="days.push({ title: '', description: '', meals: '', hotel_id: '' })" class="btn btn-ghost mt-4 w-full py-2.5"><x-icon name="plus" class="size-4"/>Add day</button>
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
                    <label class="mt-2 flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-marble-300 bg-marble-50 px-4 py-3 text-sm text-ink/60 hover:border-pine-600" x-data="{ n: 0 }">
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
                    <x-admin.input name="price" type="number" step="1" min="0" label="Price (USD pp)" :value="$package->price" required/>
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

            <x-admin.card title="Destinations visited">
                <div class="max-h-72 space-y-1 overflow-y-auto">
                    @foreach ($destinations as $d)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-marble-50">
                            <input type="checkbox" name="destination_ids[]" value="{{ $d->id }}" @checked(in_array($d->id, $selectedDestinations, true)) class="size-4 rounded accent-pine-700">
                            {{ $d->name }} <span class="ml-auto text-xs text-ink/40">{{ $d->region }}</span>
                        </label>
                    @endforeach
                </div>
            </x-admin.card>

            <button class="btn btn-primary w-full">Save journey</button>
        </div>
    </form>
</x-admin-layout>
