{{-- One page-builder field. Expects $key, $field, $value, plus $options (dynamic lists) from the editor. --}}
@php
    $name = "data[$key]";
    $id = 'f_'.$key;
    $type = $field['type'];
    $live = in_array($type, ['select', 'choice', 'layout', 'toggle'], true);
    $condition = collect($field['show'] ?? [])->map(fn ($allowed, $other) => json_encode(array_values($allowed)).'.includes(v.'.$other.')')->implode(' && ');
    $choices = match ($type) {
        'package' => ['' => '— None —'] + $options['package'],
        'category' => ['' => '— Choose —'] + $options['category'],
        'country' => ['' => '— Any —'] + $options['country'],
        'icon' => array_combine(\App\Support\SectionTypes::ICONS, \App\Support\SectionTypes::ICONS),
        default => $field['options'] ?? [],
    };
@endphp
<div @if ($condition) x-show="{{ $condition }}" x-cloak @endif class="min-w-0">
    @if ($type === 'toggle')
        <label class="flex cursor-pointer items-start gap-3">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1" x-model="v.{{ $key }}" @checked($value) class="peer sr-only">
            <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-brand-700 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-600/20 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
            <span>
                <span class="block text-sm font-medium text-ink">{{ $field['label'] }}</span>
                @isset($field['help'])<span class="block text-xs text-ink/50">{{ $field['help'] }}</span>@endisset
            </span>
        </label>

    @elseif ($type === 'layout')
        <span class="label">{{ $field['label'] }}</span>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($choices as $option => $label)
                <label class="group cursor-pointer">
                    <input type="radio" name="{{ $name }}" value="{{ $option }}" x-model="v.{{ $key }}" @checked($value === $option) class="peer sr-only">
                    <span class="block rounded-2xl p-2 ring-1 ring-slate-200 transition group-hover:ring-slate-400 peer-checked:bg-brand-50 peer-checked:ring-2 peer-checked:ring-brand-600">
                        @include('admin.pages.layout-thumb', ['layout' => $option])
                        <span class="mt-2 block px-1 text-xs leading-snug font-semibold text-slate-800">{{ $label }}</span>
                    </span>
                </label>
            @endforeach
        </div>

    @elseif ($type === 'choice')
        <span class="label">{{ $field['label'] }}</span>
        <div class="flex flex-wrap gap-2">
            @foreach ($choices as $option => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="{{ $name }}" value="{{ $option }}" x-model="v.{{ $key }}" @checked((string) $value === (string) $option) class="peer sr-only">
                    <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-300 transition hover:ring-slate-400 peer-checked:bg-brand-700 peer-checked:text-white peer-checked:ring-brand-700 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-600/25">
                        @if ($key === 'image_mask' || $key === 'shape')
                            @include('admin.pages.mask-thumb', ['mask' => $option])
                        @endif
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
        @isset($field['help'])<p class="mt-1.5 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset

    @elseif (in_array($type, ['select', 'package', 'category', 'country', 'icon'], true))
        <label for="{{ $id }}" class="label">{{ $field['label'] }}</label>
        <select id="{{ $id }}" name="{{ $name }}" class="field" @if ($live) x-model="v.{{ $key }}" @endif>
            @foreach ($choices as $option => $label)
                <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $label }}</option>
            @endforeach
        </select>
        @isset($field['help'])<p class="mt-1 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset

    @elseif ($type === 'checkboxes')
        <span class="label">{{ $field['label'] }}</span>
        <div class="flex flex-wrap gap-2">
            @foreach ($choices as $option => $label)
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-sm font-medium ring-1 ring-slate-300 has-checked:bg-brand-50 has-checked:ring-brand-600">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" @checked(in_array($option, (array) $value, true)) class="accent-brand-700">{{ $label }}
                </label>
            @endforeach
        </div>

    @elseif ($type === 'number')
        <label for="{{ $id }}" class="label">{{ $field['label'] }}</label>
        <input id="{{ $id }}" type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $field['min'] ?? 0 }}" max="{{ $field['max'] ?? '' }}" class="field max-w-40">
        @isset($field['help'])<p class="mt-1 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset

    @elseif ($type === 'range')
        <div x-data="{ n: {{ (int) $value }} }">
            <label for="{{ $id }}" class="label flex justify-between">{{ $field['label'] }} <span class="font-mono text-slate-500" x-text="n + '%'"></span></label>
            <input id="{{ $id }}" type="range" name="{{ $name }}" x-model="n" min="{{ $field['min'] }}" max="{{ $field['max'] }}" step="{{ $field['step'] ?? 1 }}" class="w-full accent-brand-700">
        </div>

    @elseif ($type === 'textarea')
        <label for="{{ $id }}" class="label">{{ $field['label'] }}</label>
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 3 }}" class="field">{{ $value }}</textarea>
        @isset($field['help'])<p class="mt-1 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset

    @elseif ($type === 'image')
        <div x-data="{ url: @js($value), preview: @js(media_url($value)) }">
            <span class="label">{{ $field['label'] }}</span>
            <div class="flex flex-col gap-4 sm:flex-row">
                <x-media class="aspect-[4/3] w-full shrink-0 rounded-xl sm:w-40">
                    <img x-show="preview" :src="preview" alt="" class="absolute inset-0 size-full object-cover" x-on:error="$el.style.display='none'" x-on:load="$el.style.display=''">
                </x-media>
                <div class="min-w-0 flex-1 space-y-2">
                    <input type="text" name="{{ $name }}" x-model="url" @input="preview = url" placeholder="Paste an image URL…" class="field">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-ink/60 transition hover:border-brand-600 hover:text-brand-800">
                        <x-icon name="plus" class="size-4"/><span>…or upload a photo</span>
                        <input type="file" name="files[{{ $key }}]" accept="image/*" class="sr-only" @change="if ($event.target.files[0]) preview = URL.createObjectURL($event.target.files[0])">
                    </label>
                    @isset($field['help'])<p class="text-xs text-ink/50">{{ $field['help'] }}</p>@endisset
                    @error("files.$key")<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

    @elseif ($type === 'video')
        <div x-data="{ chosen: '' }">
            <span class="label">{{ $field['label'] }}</span>
            <input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="Paste a link to an .mp4 file, or upload below" class="field">
            <label class="mt-2 flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-ink/60 transition hover:border-brand-600 hover:text-brand-800">
                <x-icon name="plus" class="size-4"/><span x-text="chosen || 'Upload a video'"></span>
                <input type="file" name="files[{{ $key }}]" accept="video/mp4,video/webm" class="sr-only" @change="chosen = $event.target.files[0]?.name || ''">
            </label>
            @isset($field['help'])<p class="mt-1 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset
            @error("files.$key")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

    @elseif ($type === 'repeater')
        @php
            $sub = $field['fields'];
            $blank = array_map(fn ($f) => $f['default'], $sub);
        @endphp
        <div x-data="{
                items: @js(collect($value)->map(fn ($item) => array_replace($blank, $item) + ['_k' => Str::random(8)])->values()),
                blank: @js($blank),
                max: {{ $field['max_items'] ?? 50 }},
                storage: @js(rtrim(Storage::disk('public')->url(''), '/').'/'),
                src(value) { return !value || value.startsWith('wiki:') ? '' : (/^(https?:)?\//.test(value) ? value : this.storage + value) },
                add() { this.items.push({ ...this.blank, _k: Math.random().toString(36).slice(2) }) },
                move(i, step) { const j = i + step; if (j < 0 || j >= this.items.length) return; [this.items[i], this.items[j]] = [this.items[j], this.items[i]] },
             }">
            <div class="mb-2 flex items-baseline justify-between gap-3">
                <span class="label mb-0">{{ $field['label'] }}</span>
                <span class="text-xs text-ink/50" x-text="items.length + ' / ' + max"></span>
            </div>
            @isset($field['help'])<p class="mb-3 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset
            <div class="space-y-3">
                <template x-for="(item, i) in items" :key="item._k">
                    <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500" x-text="'#' + (i + 1)"></span>
                            <div class="flex">
                                <button type="button" @click="move(i, -1)" :disabled="i === 0" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-white disabled:opacity-30" aria-label="Move up">↑</button>
                                <button type="button" @click="move(i, 1)" :disabled="i === items.length - 1" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-white disabled:opacity-30" aria-label="Move down">↓</button>
                                <button type="button" @click="items.splice(i, 1)" class="rounded-lg px-2 py-1 text-rose-600 hover:bg-rose-50" aria-label="Remove"><x-icon name="trash" class="size-4"/></button>
                            </div>
                        </div>
                        <div @class(['grid gap-2', 'sm:grid-cols-[8rem_1fr]' => isset($sub['icon']) && count($sub) === 2])>
                            @foreach ($sub as $subKey => $subField)
                                @if ($subField['type'] === 'image')
                                    <div class="flex gap-3">
                                        <div class="media-fallback relative size-20 shrink-0 overflow-hidden rounded-lg">
                                            <img x-show="item._preview || src(item.{{ $subKey }})" :src="item._preview || src(item.{{ $subKey }})" alt="" class="absolute inset-0 size-full object-cover">
                                        </div>
                                        <div class="min-w-0 flex-1 space-y-1.5">
                                            <input type="text" :name="`data[{{ $key }}][${i}][{{ $subKey }}]`" x-model="item.{{ $subKey }}" @input="item._preview = ''" class="field py-2" placeholder="Photo URL">
                                            <label class="flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-brand-700 hover:underline">
                                                <x-icon name="plus" class="size-3.5"/>Upload a photo
                                                <input type="file" :name="`files[{{ $key }}][${i}][{{ $subKey }}]`" accept="image/*" class="sr-only" @change="if ($event.target.files[0]) item._preview = URL.createObjectURL($event.target.files[0])">
                                            </label>
                                        </div>
                                    </div>
                                @elseif ($subField['type'] === 'icon')
                                    <select :name="`data[{{ $key }}][${i}][{{ $subKey }}]`" x-model="item.{{ $subKey }}" class="field py-2" aria-label="{{ $subField['label'] }}">
                                        @foreach (\App\Support\SectionTypes::ICONS as $icon)<option value="{{ $icon }}">{{ $icon }}</option>@endforeach
                                    </select>
                                @elseif ($subField['type'] === 'textarea')
                                    <textarea :name="`data[{{ $key }}][${i}][{{ $subKey }}]`" x-model="item.{{ $subKey }}" rows="2" class="field py-2" placeholder="{{ $subField['label'] }}"></textarea>
                                @else
                                    <input type="text" :name="`data[{{ $key }}][${i}][{{ $subKey }}]`" x-model="item.{{ $subKey }}" class="field py-2" placeholder="{{ $subField['label'] }}">
                                @endif
                            @endforeach
                        </div>
                    </div>
                </template>
            </div>
            <button type="button" @click="add()" x-show="items.length < max" class="btn btn-ghost mt-3 py-2"><x-icon name="plus" class="size-4"/>Add</button>
        </div>

    @else
        <label for="{{ $id }}" class="label">{{ $field['label'] }}</label>
        <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $value }}" class="field" @if (str_ends_with($key, '_url')) list="site-links" placeholder="/contact or https://…" @endif>
        @isset($field['help'])<p class="mt-1 text-xs text-ink/50">{{ $field['help'] }}</p>@endisset
    @endif
</div>
