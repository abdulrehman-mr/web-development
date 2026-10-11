<x-admin-layout title="Destinations">
    <x-slot:actions><a href="{{ route('admin.destinations.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">New destination</span></a></x-slot:actions>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($destinations as $d)
            <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200">
                <x-media :src="$d->image_url" class="aspect-[16/9]">
                    @if ($d->is_featured)<span class="absolute top-3 left-3 rounded-full bg-lake-300 px-2.5 py-1 text-xs font-semibold text-slate-900">Featured</span>@endif
                </x-media>
                <div class="p-4">
                    <p class="text-xs text-brand-600">{{ $d->country_name }}{{ $d->region ? ' · '.$d->region : '' }}</p>
                    <p class="font-semibold text-slate-900">{{ $d->name }}</p>
                    <p class="mt-1 text-xs text-ink/50">{{ $d->packages_count }} journeys · {{ $d->hotels_count }} stays</p>
                    <div class="mt-3 flex gap-1">
                        <a href="{{ route('admin.destinations.edit', $d) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                        <x-admin.delete :action="route('admin.destinations.destroy', $d)"/>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
