<x-admin-layout title="Experiences">
    <x-slot:actions><a href="{{ route('admin.categories.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">New experience</span></a></x-slot:actions>
    <p class="mb-4 text-sm text-ink/60">Experiences group your journeys (e.g. Sikh Yatra, Northern Highlands) and appear in the main menu.</p>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($categories as $c)
            <div class="flex min-w-0 gap-4 rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                <x-media :src="$c->image_url" class="size-20 shrink-0 rounded-xl"/>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900">{{ $c->name }}</p>
                    <p class="truncate text-xs text-ink/50">{{ $c->tagline }}</p>
                    <p class="mt-1 text-xs text-ink/50">{{ $c->packages_count }} journeys</p>
                    <div class="mt-1 -ml-2.5 flex">
                        <a href="{{ route('admin.categories.edit', $c) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                        <x-admin.delete :action="route('admin.categories.destroy', $c)" confirm="Delete this experience? Its journeys will become uncategorised."/>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
