<x-admin-layout title="Stays">
    <x-slot:actions><a href="{{ route('admin.hotels.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">New stay</span></a></x-slot:actions>
    <div class="overflow-x-auto rounded-2xl bg-white ring-1 ring-slate-200">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="border-b border-slate-200 text-xs text-ink/50"><tr><th class="px-5 py-3 font-medium">Name</th><th class="py-3 font-medium">Type</th><th class="py-3 font-medium">Destination</th><th class="py-3 font-medium">Stars</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-200">
                @foreach ($hotels as $h)
                    <tr>
                        <td class="px-5 py-3"><a href="{{ route('admin.hotels.edit', $h) }}" class="font-semibold text-slate-900 hover:underline">{{ $h->name }}</a></td>
                        <td class="py-3 text-ink/70">{{ $h->type }}</td>
                        <td class="py-3 text-ink/70">{{ $h->destination?->name }}</td>
                        <td class="py-3 text-slate-900">{{ str_repeat('★', $h->stars) }}</td>
                        <td class="py-3 pr-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.hotels.edit', $h) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                            <x-admin.delete :action="route('admin.hotels.destroy', $h)"/>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
