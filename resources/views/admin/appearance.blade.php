<x-admin-layout title="Appearance">
    <x-slot:actions><button form="appearance-form" class="btn btn-primary py-2.5">Save scheme</button></x-slot:actions>

    <form id="appearance-form" method="post" action="{{ route('admin.appearance.update') }}"
          x-data="themePicker(@js($presets), @js(setting('theme_preset', 'pakistan')), @js(setting('theme_brand', $colors['brand'])), @js(setting('theme_accent', $colors['accent'])))"
          class="grid gap-6 xl:grid-cols-5">
        @csrf @method('PUT')

        <div class="space-y-6 xl:col-span-3">
            <x-admin.card title="Colour scheme" description="Choose a ready-made scheme or pick your own two colours. Every shade is generated automatically.">
                <div class="grid gap-3 sm:grid-cols-2">
                    <template x-for="(preset, key) in presets" :key="key">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl p-3 ring-1 transition" :class="selected === key ? 'bg-slate-50 ring-2 ring-slate-900' : 'ring-slate-200 hover:ring-slate-300'">
                            <input type="radio" name="theme_preset" :value="key" x-model="selected" class="sr-only">
                            <span class="flex -space-x-1.5">
                                <span class="size-8 rounded-full ring-2 ring-white" :style="`background:${preset.brand}`"></span>
                                <span class="size-8 rounded-full ring-2 ring-white" :style="`background:${preset.accent}`"></span>
                            </span>
                            <span class="text-sm font-semibold text-slate-900" x-text="preset.label"></span>
                        </label>
                    </template>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl p-3 ring-1 transition" :class="selected === 'custom' ? 'bg-slate-50 ring-2 ring-slate-900' : 'ring-slate-200 hover:ring-slate-300'">
                        <input type="radio" name="theme_preset" value="custom" x-model="selected" class="sr-only">
                        <span class="grid size-8 place-items-center rounded-full bg-slate-100 text-slate-500 ring-2 ring-white"><x-icon name="plus" class="size-4"/></span>
                        <span class="text-sm font-semibold text-slate-900">Custom colours</span>
                    </label>
                </div>

                <div x-show="selected === 'custom'" x-collapse class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach (['brand' => ['Main colour', 'Buttons, links, footer and highlights.'], 'accent' => ['Accent colour', 'Small highlights on dark sections.']] as $key => [$label, $help])
                        <div>
                            <label class="label" for="f_{{ $key }}">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="{{ $key }}" class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1" aria-label="{{ $label }} picker">
                                <input id="f_{{ $key }}" name="theme_{{ $key }}" x-model="{{ $key }}" pattern="#[0-9a-fA-F]{6}" class="field font-mono uppercase">
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $help }}</p>
                        </div>
                    @endforeach
                    <p class="text-xs text-slate-500 sm:col-span-2">Tip: choose a main colour dark enough for white text on top of it.</p>
                </div>
            </x-admin.card>
        </div>

        {{-- Live preview --}}
        <div class="xl:col-span-2">
            <x-admin.card title="Preview" class="xl:sticky xl:top-24">
                <div :style="previewStyle" class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                    <div class="bg-white p-5">
                        <p class="text-sm font-semibold text-brand-700">Sikh Yatra</p>
                        <p class="mt-1 text-xl font-bold tracking-tight text-slate-900">Footsteps of Guru Nanak</p>
                        <p class="mt-1 text-sm text-slate-500">9 days · from Lahore</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="btn btn-primary py-2">Request this journey</span>
                            <span class="rounded-full bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-800 ring-1 ring-brand-100">Airport welcome</span>
                        </div>
                    </div>
                    <div class="bg-brand-900 p-5 text-white">
                        <p class="text-sm font-semibold text-lake-300">For the sangat</p>
                        <p class="mt-1 font-bold">Dark sections and the footer</p>
                        <p class="mt-2 flex items-center gap-2 text-sm text-white/80"><x-icon name="check" class="size-4 text-lake-300"/>Accent on dark backgrounds</p>
                        <span class="btn btn-light mt-4 py-2">See the full yatra</span>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </form>

    <script>
        function themePicker(presets, selected, brand, accent) {
            // Mirrors App\Support\Theme so the preview matches the live site.
            const brandShades = {50: 6, 100: 12, 200: 25, 300: 42, 400: 62, 500: 80, 600: 90, 700: 100, 800: -82, 900: -64, 950: -42};
            const accentShades = {50: 8, 100: 18, 200: 34, 300: 55, 400: 78, 500: 100, 600: -86, 700: -72, 800: -60, 900: -50};
            const scale = (name, hex, shades) => Object.entries(shades).map(([shade, mix]) =>
                `--color-${name}-${shade}:` + (mix === 100 ? hex : `color-mix(in oklab, ${hex} ${Math.abs(mix)}%, ${mix > 0 ? 'white' : 'black'})`)).join(';');
            return {
                presets, selected, brand, accent,
                get colors() { return this.selected === 'custom' ? { brand: this.brand, accent: this.accent } : this.presets[this.selected] },
                get previewStyle() { return scale('brand', this.colors.brand, brandShades) + ';' + scale('lake', this.colors.accent, accentShades) },
            };
        }
    </script>
</x-admin-layout>
