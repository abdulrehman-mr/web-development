<x-admin-layout title="Reviews">
    <x-slot:actions><a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">Add review</span></a></x-slot:actions>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($testimonials as $t)
            <figure class="rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                <div class="flex items-center justify-between"><span class="text-slate-900">{{ str_repeat('★', $t->rating) }}</span>@unless($t->is_published)<span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs">Hidden</span>@endunless</div>
                <blockquote class="mt-2 line-clamp-3 text-sm text-ink/75">“{{ $t->quote }}”</blockquote>
                <figcaption class="mt-3 text-sm"><span class="font-semibold">{{ $t->name }}</span> <span class="text-ink/50">· {{ $t->location }}</span></figcaption>
                <div class="mt-2 -ml-2.5 flex">
                    <a href="{{ route('admin.testimonials.edit', $t) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                    <x-admin.delete :action="route('admin.testimonials.destroy', $t)"/>
                </div>
            </figure>
        @endforeach
    </div>
</x-admin-layout>
