<x-admin-layout title="Room types">
    <x-slot:actions><button form="room-types-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>

    <form id="room-types-form" method="post" action="{{ route('admin.room-types.update') }}" class="max-w-4xl"
          x-data="{ types: @js($types->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'description' => $t->description, 'default_adjustment' => $t->default_adjustment, 'is_active' => $t->is_active])->values()) }">
        @csrf @method('PUT')
        <x-admin.card title="Room options guests can choose" :description="'Prices are per adult for the whole trip, in '.currencies()->base()->code.'. Use 0 for the standard price and a negative number for a discount. Each journey can switch rooms on or off and override the price.'">
            <div class="space-y-3">
                <template x-for="(type, i) in types" :key="i">
                    <div class="grid gap-2 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 sm:grid-cols-[1fr_9rem_auto_auto] sm:items-center">
                        <input type="hidden" :name="`types[${i}][id]`" :value="type.id ?? ''">
                        <input :name="`types[${i}][name]`" x-model="type.name" placeholder="Name, e.g. Single room" class="field py-2" required>
                        <input type="number" step="1" :name="`types[${i}][default_adjustment]`" x-model="type.default_adjustment" class="field py-2" aria-label="Price change per adult" required>
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" :name="`types[${i}][is_active]`" value="1" x-model="type.is_active" class="size-4 accent-brand-700"> Active</label>
                        <div class="flex gap-1">
                            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-white disabled:opacity-30" :disabled="i === 0" @click="[types[i - 1], types[i]] = [types[i], types[i - 1]]" aria-label="Move up">↑</button>
                            <button type="button" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" @click="types.splice(i, 1)" aria-label="Remove"><x-icon name="trash" class="size-4"/></button>
                        </div>
                        <input :name="`types[${i}][description]`" x-model="type.description" placeholder="What it means for the guest, e.g. Two guests share a room with two beds" class="field py-2 sm:col-span-4">
                    </div>
                </template>
            </div>
            <button type="button" @click="types.push({ id: null, name: '', description: '', default_adjustment: 0, is_active: true })" class="btn btn-ghost mt-4 py-2"><x-icon name="plus" class="size-4"/>Add room type</button>
        </x-admin.card>
    </form>
</x-admin-layout>
