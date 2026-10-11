<x-admin-layout :title="'Page: '.$page->title">
    <x-slot:actions>
        <a href="{{ $page->url() }}" target="_blank" class="btn btn-ghost py-2.5"><x-icon name="external" class="size-4"/><span class="hidden sm:inline">View page</span></a>
        <button type="button" @click="$dispatch('add-section', { after: '' })" class="btn btn-primary py-2.5"><x-icon name="plus" class="size-4"/><span class="hidden sm:inline">Add section</span></button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Sections --}}
        <div class="min-w-0"
             x-data="{
                order: @js($page->sections->pluck('id')),
                dragging: null,
                saved: false,
                move(id, step) {
                    const i = this.order.indexOf(id), j = i + step;
                    if (j < 0 || j >= this.order.length) return;
                    [this.order[i], this.order[j]] = [this.order[j], this.order[i]];
                    this.save();
                },
                over(id) {
                    if (this.dragging === null || this.dragging === id) return;
                    const from = this.order.indexOf(this.dragging), to = this.order.indexOf(id);
                    this.order.splice(from, 1);
                    this.order.splice(to, 0, this.dragging);
                },
                save() {
                    fetch(@js(route('admin.pages.sections.reorder', $page)), {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ order: this.order }),
                    }).then((r) => { this.saved = r.ok; setTimeout(() => this.saved = false, 1500); });
                },
             }">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm text-ink/60">Drag <x-icon name="drag" class="inline size-4 align-text-bottom"/> or use the arrows to reorder. Changes to the order save straight away.</p>
                <span x-cloak x-show="saved" x-transition class="shrink-0 text-xs font-semibold text-brand-700">Order saved</span>
            </div>

            @if ($page->sections->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="font-semibold text-slate-900">No sections yet</p>
                    <p class="mt-1 text-sm text-ink/60">Start with a hero, then add journeys, text, photos or a contact form.</p>
                    <button type="button" @click="$dispatch('add-section', { after: '' })" class="btn btn-primary mt-5"><x-icon name="plus" class="size-4"/>Add the first section</button>
                </div>
            @endif

            <div class="flex flex-col gap-2">
                @foreach ($page->sections as $section)
                    @php $type = \App\Support\SectionTypes::get($section->type); @endphp
                    <div :style="'order:' + order.indexOf({{ $section->id }})"
                         draggable="true"
                         @dragstart="dragging = {{ $section->id }}; $event.dataTransfer.effectAllowed = 'move'"
                         @dragover.prevent="over({{ $section->id }})"
                         @dragend="dragging = null; save()"
                         :class="dragging === {{ $section->id }} ? 'opacity-50 ring-brand-600' : ''"
                         @class(['group flex items-center gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-200 transition sm:p-4', 'opacity-60' => ! $section->is_enabled])>
                        <span class="hidden cursor-grab text-slate-400 active:cursor-grabbing sm:block" aria-hidden="true"><x-icon name="drag" class="size-5"/></span>
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-icon :name="$type['icon'] ?? 'grid'" class="size-5"/></span>
                        <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="truncate font-semibold text-slate-900">{{ $section->label() }}</span>
                                @unless ($section->is_enabled)<span class="shrink-0 rounded-full bg-slate-200 px-2 py-0.5 text-xs">Hidden</span>@endunless
                            </span>
                            <span class="block truncate text-sm text-ink/55">{{ $section->summary() }}</span>
                        </a>
                        <div class="flex shrink-0 items-center">
                            <button type="button" @click="move({{ $section->id }}, -1)" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 sm:block" aria-label="Move up">↑</button>
                            <button type="button" @click="move({{ $section->id }}, 1)" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 sm:block" aria-label="Move down">↓</button>
                            <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/><span class="hidden md:inline">Edit</span></a>
                            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                                <button type="button" @click="menu = !menu" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More actions">⋯</button>
                                <div x-cloak x-show="menu" x-transition.opacity.duration.100ms class="absolute top-full right-0 z-20 mt-1 w-52 rounded-xl bg-white p-1.5 text-sm shadow-xl ring-1 ring-slate-200">
                                    <button type="button" @click="menu = false; $dispatch('add-section', { after: {{ $section->id }} })" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-50"><x-icon name="plus" class="size-4 text-slate-500"/>Add section below</button>
                                    <button type="button" @click="move({{ $section->id }}, -1); menu = false" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-50 sm:hidden">↑ Move up</button>
                                    <button type="button" @click="move({{ $section->id }}, 1); menu = false" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-50 sm:hidden">↓ Move down</button>
                                    <form method="post" action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}">@csrf @method('PATCH')
                                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-50"><x-icon name="eye" class="size-4 text-slate-500"/>{{ $section->is_enabled ? 'Hide on site' : 'Show on site' }}</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.pages.sections.duplicate', [$page, $section]) }}">@csrf
                                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-50"><x-icon name="document" class="size-4 text-slate-500"/>Duplicate</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.pages.sections.destroy', [$page, $section]) }}" onsubmit="return confirm('Remove this section? This cannot be undone.')">@csrf @method('DELETE')
                                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-rose-600 hover:bg-rose-50"><x-icon name="trash" class="size-4"/>Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Page settings --}}
        <div class="min-w-0">
            <form method="post" action="{{ route('admin.pages.update', $page) }}" class="xl:sticky xl:top-24">
                @csrf @method('PUT')
                <x-admin.card title="Page settings">
                    <div class="grid gap-4">
                        <x-admin.input name="title" label="Title" :value="$page->title" help="Shown in the browser tab and search results." required/>
                        @if ($page->is_system)
                            <div><span class="label">Address</span><p class="font-mono text-sm text-slate-600">{{ parse_url($page->url(), PHP_URL_PATH) ?: '/' }}</p></div>
                        @else
                            <x-admin.input name="slug" label="Address" :value="$page->slug" help="Lowercase letters, numbers and dashes."/>
                        @endif
                        <x-admin.textarea name="meta_description" label="Search-engine description" :value="$page->meta_description" rows="3"/>
                        @unless ($page->is_system)
                            <x-admin.toggle name="is_published" label="Published" :checked="$page->is_published" help="Drafts are visible only while you are signed in."/>
                            <x-admin.toggle name="show_in_menu" label="Show in the main menu" :checked="$page->show_in_menu"/>
                        @endunless
                        <button class="btn btn-primary">Save settings</button>
                    </div>
                </x-admin.card>
            </form>
        </div>
    </div>

    {{-- Section picker --}}
    <div x-data="{ open: false, after: '' }" @add-section.window="open = true; after = $event.detail.after" @keydown.escape.window="open = false" x-cloak x-show="open" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-0 sm:items-center sm:p-6" x-transition.opacity>
        <div @click.outside="open = false" class="max-h-[90dvh] w-full max-w-4xl overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-7">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Add a section</h2>
                <button type="button" @click="open = false" class="rounded-lg p-2 hover:bg-slate-100" aria-label="Close"><x-icon name="x"/></button>
            </div>
            <form method="post" action="{{ route('admin.pages.sections.store', $page) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <input type="hidden" name="after" :value="after">
                @foreach ($types as $key => $t)
                    <button name="type" value="{{ $key }}" class="flex gap-3 rounded-2xl p-4 text-left ring-1 ring-slate-200 transition hover:bg-brand-50 hover:ring-brand-300">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-icon :name="$t['icon']" class="size-5"/></span>
                        <span><span class="block text-sm font-semibold text-slate-900">{{ $t['label'] }}</span><span class="mt-0.5 block text-xs leading-snug text-ink/55">{{ $t['description'] }}</span></span>
                    </button>
                @endforeach
            </form>
        </div>
    </div>
</x-admin-layout>
