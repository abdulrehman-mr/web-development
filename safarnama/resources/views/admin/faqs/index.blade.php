<x-admin-layout title="FAQs">
    <x-slot:actions><a href="{{ route('admin.faqs.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">Add question</span></a></x-slot:actions>
    <div class="divide-y divide-marble-200 rounded-2xl bg-white ring-1 ring-marble-200">
        @foreach ($faqs as $f)
            <div class="flex flex-col gap-2 p-5 sm:flex-row sm:items-start">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-pine-950">{{ $f->question }} @unless($f->is_published)<span class="ml-2 rounded-full bg-marble-200 px-2 py-0.5 text-xs font-normal">Hidden</span>@endunless</p>
                    <p class="mt-1 line-clamp-2 text-sm text-ink/60">{{ $f->answer }}</p>
                </div>
                <div class="-ml-2.5 flex shrink-0">
                    <a href="{{ route('admin.faqs.edit', $f) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-pine-800 hover:bg-pine-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                    <x-admin.delete :action="route('admin.faqs.destroy', $f)"/>
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
