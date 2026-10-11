<x-admin-layout title="Vibes">
    <x-slot:actions><button form="vibes-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>

    <form id="vibes-form" method="post" action="{{ route('admin.vibes.update') }}" class="max-w-4xl"
          x-data="{ vibes: @js($vibes->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'description' => $v->description, 'icon' => $v->icon, 'count' => $v->packages_count])->values()), icons: @js($icons) }">
        @csrf @method('PUT')
        <x-admin.card title="Travel vibes" description="Themes guests can browse by, like Romantic, Heritage or Tech-savvy. Tick them on each journey; a journey can have several.">
            <div class="space-y-3">
                <template x-for="(vibe, i) in vibes" :key="i">
                    <div class="grid gap-2 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 sm:grid-cols-[9rem_1fr_auto] sm:items-center">
                        <input type="hidden" :name="`vibes[${i}][id]`" :value="vibe.id ?? ''">
                        <select :name="`vibes[${i}][icon]`" x-model="vibe.icon" class="field py-2" aria-label="Icon">
                            <template x-for="icon in icons" :key="icon"><option :value="icon" x-text="icon" :selected="icon === vibe.icon"></option></template>
                        </select>
                        <input :name="`vibes[${i}][name]`" x-model="vibe.name" class="field py-2" placeholder="Name, e.g. Romantic" required>
                        <div class="flex items-center gap-1">
                            <span class="px-2 text-xs text-slate-500" x-text="vibe.count ? vibe.count + ' journeys' : ''"></span>
                            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-white disabled:opacity-30" :disabled="i === 0" @click="[vibes[i - 1], vibes[i]] = [vibes[i], vibes[i - 1]]" aria-label="Move up">↑</button>
                            <button type="button" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" @click="vibes.splice(i, 1)" aria-label="Remove"><x-icon name="trash" class="size-4"/></button>
                        </div>
                        <input :name="`vibes[${i}][description]`" x-model="vibe.description" class="field py-2 sm:col-span-3" placeholder="Short description shown to guests">
                    </div>
                </template>
            </div>
            <button type="button" @click="vibes.push({ id: null, name: '', description: '', icon: 'sparkle', count: 0 })" class="btn btn-ghost mt-4 py-2"><x-icon name="plus" class="size-4"/>Add vibe</button>
        </x-admin.card>
    </form>
</x-admin-layout>
