<x-admin-layout title="Pages">
    <x-slot:actions><a href="{{ route('admin.pages.create') }}" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">New page</span></a></x-slot:actions>

    <p class="mb-6 max-w-3xl text-sm text-ink/60">Every page is built from sections you can add, remove, hide, reorder and edit: the hero, journeys, reviews, text, photos and more. Pages you create get their own address, like <span class="font-mono">/umrah-guide</span>, and can be added to the menu.</p>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($pages as $p)
            <div class="flex flex-col rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900">{{ $p->title }}</p>
                        <p class="mt-0.5 truncate font-mono text-xs text-slate-500">{{ parse_url($p->url(), PHP_URL_PATH) ?: '/' }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap justify-end gap-1">
                        @if ($p->is_system)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Built-in</span>@endif
                        @unless ($p->is_published)<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Draft</span>@endunless
                        @if ($p->show_in_menu)<span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-800">In menu</span>@endif
                    </div>
                </div>
                <p class="mt-3 text-sm text-ink/60">{{ $p->sections_count }} {{ Str::plural('section', $p->sections_count) }}</p>
                <div class="mt-4 -ml-2.5 flex flex-wrap gap-1 border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.pages.edit', $p) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit sections</a>
                    <a href="{{ $p->url() }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"><x-icon name="external" class="size-4"/>View</a>
                    @unless ($p->is_system)
                        <x-admin.delete :action="route('admin.pages.destroy', $p)" confirm="Delete this page and all its sections?"/>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
