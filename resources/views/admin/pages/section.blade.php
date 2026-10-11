@php
    $fields = $type['fields'];
    $tabOrder = $section->type === 'hero' ? ['Layout', 'Media', 'Content', 'Search', 'Style'] : ['Content', 'Layout', 'Media', 'Search', 'Style'];
    $tabs = collect($tabOrder)->filter(fn ($tab) => collect($fields)->contains(fn ($f) => $f['tab'] === $tab))->values();
    // Values that other fields depend on, kept live in Alpine so options appear and hide as you choose.
    $live = collect($fields)->filter(fn ($f) => in_array($f['type'], ['select', 'choice', 'layout', 'toggle'], true))->keys()
        ->mapWithKeys(fn ($key) => [$key => $values[$key]])->all();
    $previewUrl = $page->url().'#section-'.$section->id;
@endphp
<x-admin-layout :title="$section->label().' · '.$page->title">
    <x-slot:actions>
        <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-ghost py-2.5"><x-icon name="arrow-left" class="size-4"/><span class="hidden sm:inline">All sections</span></a>
        <button form="section-form" class="btn btn-primary py-2.5">Save</button>
    </x-slot:actions>

    <div class="grid gap-6 2xl:grid-cols-[minmax(0,40rem)_minmax(0,1fr)]" x-data="{ preview: window.innerWidth >= 1536 }">
        <form id="section-form" method="post" enctype="multipart/form-data" action="{{ route('admin.pages.sections.update', [$page, $section]) }}"
              class="min-w-0" x-data="{ tab: @js($tabs->first()), v: @js((object) $live) }">
            @csrf @method('PUT')

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <x-admin.toggle name="is_enabled" label="Show this section on the site" :checked="$section->is_enabled"/>
                <button type="button" @click="preview = !preview" class="text-sm font-semibold text-brand-700 hover:underline" x-text="preview ? 'Hide preview' : 'Show preview'"></button>
            </div>

            @if ($tabs->count() > 1)
                <div class="sticky top-16 z-20 -mx-4 mb-4 overflow-x-auto bg-slate-100/95 px-4 py-2 backdrop-blur sm:mx-0 sm:px-0">
                    <div class="inline-flex gap-1 rounded-xl bg-white p-1 ring-1 ring-slate-200">
                        @foreach ($tabs as $tab)
                            <button type="button" @click="tab = @js($tab)" :class="tab === @js($tab) ? 'bg-brand-700 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">{{ $tab }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($tabs as $tab)
                <div x-show="tab === @js($tab)" @if (! $loop->first) x-cloak @endif>
                    <x-admin.card>
                        <div class="grid gap-6">
                            @foreach ($fields as $key => $field)
                                @continue($field['tab'] !== $tab)
                                @include('admin.pages.field', ['key' => $key, 'field' => $field, 'value' => $values[$key] ?? $field['default']])
                            @endforeach
                        </div>
                    </x-admin.card>
                </div>
            @endforeach

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <button class="btn btn-primary">Save section</button>
                <a href="{{ $previewUrl }}" target="_blank" class="text-sm font-semibold text-brand-700 hover:underline">Open the page in a new tab</a>
            </div>

            <datalist id="site-links">
                @foreach ($links as $path => $label)<option value="{{ $path }}">{{ $label }}</option>@endforeach
            </datalist>
        </form>

        {{-- Live preview of the saved page, scaled from desktop or phone width. --}}
        <div x-cloak x-show="preview" class="min-w-0"
             x-data="{ device: 'desktop', scale: 1, width() { return this.device === 'desktop' ? 1280 : 390 }, fit() { this.scale = Math.min(1, this.$refs.frame.clientWidth / this.width()) } }"
             x-init="$nextTick(() => fit()); new ResizeObserver(() => fit()).observe($refs.frame)">
            <div class="sticky top-20">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-700">Preview <span class="font-normal text-ink/50">· updates when you save</span></p>
                    <div class="inline-flex rounded-lg bg-white p-0.5 text-xs font-semibold ring-1 ring-slate-200">
                        <button type="button" @click="device = 'desktop'; $nextTick(() => fit())" :class="device === 'desktop' ? 'bg-slate-900 text-white' : 'text-slate-600'" class="rounded-md px-3 py-1.5">Desktop</button>
                        <button type="button" @click="device = 'phone'; $nextTick(() => fit())" :class="device === 'phone' ? 'bg-slate-900 text-white' : 'text-slate-600'" class="rounded-md px-3 py-1.5">Phone</button>
                    </div>
                </div>
                <div x-ref="frame" class="h-[calc(100dvh-9rem)] min-h-[28rem] overflow-hidden rounded-2xl bg-white ring-1 ring-slate-300">
                    <iframe src="{{ $previewUrl }}" title="Page preview" class="origin-top-left border-0"
                            :style="`width:${width()}px; height:${100 / scale}%; transform:scale(${scale}); margin-left:${device === 'phone' ? Math.max(0, ($refs.frame.clientWidth - width() * scale) / 2) : 0}px`"></iframe>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
