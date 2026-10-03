<x-admin-layout title="Journeys">
    <x-slot:actions><a href="{{ route('admin.packages.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">New journey</span></a></x-slot:actions>

    <form class="mb-4"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search journeys…" class="field max-w-sm bg-white"></form>

    <div class="grid gap-3">
        @forelse ($packages as $p)
            <div class="flex flex-col gap-4 rounded-2xl bg-white p-3 ring-1 ring-marble-200 sm:flex-row sm:items-center">
                <x-media :src="$p->hero_image_url" class="aspect-[16/9] w-full shrink-0 rounded-xl sm:aspect-[4/3] sm:w-32"/>
                <div class="min-w-0 flex-1 px-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.packages.edit', $p) }}" class="font-semibold text-pine-950 hover:underline">{{ $p->title }}</a>
                        @if (! $p->is_published)<span class="rounded-full bg-marble-200 px-2 py-0.5 text-xs text-ink/60">Draft</span>@endif
                        @if ($p->is_featured)<span class="rounded-full bg-brass-100 px-2 py-0.5 text-xs text-brass-700">Featured</span>@endif
                    </div>
                    <p class="mt-1 text-sm text-ink/55">{{ $p->category?->name ?? 'Uncategorised' }} · {{ $p->duration_days }} days · {{ money($p->price) }} · {{ $p->bookings_count }} {{ Str::plural('booking', $p->bookings_count) }}</p>
                </div>
                <div class="flex items-center gap-1 px-1">
                    <a href="{{ route('packages.show', $p) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-ink/60 hover:bg-marble-100"><x-icon name="eye" class="size-4"/>View</a>
                    <a href="{{ route('admin.packages.edit', $p) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-pine-800 hover:bg-pine-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                    <x-admin.delete :action="route('admin.packages.destroy', $p)"/>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center ring-1 ring-marble-200">
                <p class="text-ink/60">No journeys yet.</p>
                <a href="{{ route('admin.packages.create') }}" class="btn btn-primary mt-4">Create your first journey</a>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $packages->links() }}</div>
</x-admin-layout>
